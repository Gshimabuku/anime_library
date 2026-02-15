<?php

namespace App\Services\Impl;

use App\Models\Member;
use App\Models\MemberSeriesStatus;
use App\Enums\WatchStatus;
use App\Services\MemberService;
use Illuminate\Pagination\LengthAwarePaginator;

class MemberServiceImpl implements MemberService
{
    /**
     * {@inheritdoc}
     */
    public function getMembers(array $searchParams): LengthAwarePaginator
    {
        $keyword = $searchParams['keyword'] ?? null;
        $watchedCountMin = $searchParams['watched_count_min'] ?? null;
        $watchedCountMax = $searchParams['watched_count_max'] ?? null;

        return Member::query()
            ->where('is_active', true)
            ->addSelect([
                'watched_anime_titles_count' => MemberSeriesStatus::query()
                    ->join('series', 'member_series_statuses.series_id', '=', 'series.id')
                    ->whereColumn('member_series_statuses.member_id', 'members.id')
                    ->where('member_series_statuses.status', \App\Enums\WatchStatus::WATCHED->value)
                    ->selectRaw('COUNT(DISTINCT series.anime_title_id)')
            ])
            ->when($keyword, fn ($q) => $q->where('name', 'like', "%{$keyword}%"))
            ->when($watchedCountMin !== null, fn ($q) => $q->whereRaw(
                '(SELECT COUNT(DISTINCT s.anime_title_id) FROM member_series_statuses mss JOIN series s ON mss.series_id = s.id WHERE mss.member_id = members.id AND mss.status = ?) >= ?',
                [\App\Enums\WatchStatus::WATCHED->value, $watchedCountMin]
            ))
            ->when($watchedCountMax !== null, fn ($q) => $q->whereRaw(
                '(SELECT COUNT(DISTINCT s.anime_title_id) FROM member_series_statuses mss JOIN series s ON mss.series_id = s.id WHERE mss.member_id = members.id AND mss.status = ?) <= ?',
                [\App\Enums\WatchStatus::WATCHED->value, $watchedCountMax]
            ))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);
    }

    /**
     * {@inheritdoc}
     */
    public function createMember(array $data): Member
    {
        $maxOrder = Member::max('sort_order') ?? 0;
        $data['sort_order'] = $maxOrder + 1;

        return Member::create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateMember(Member $member, array $data): Member
    {
        $member->update($data);
        return $member;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMember(Member $member): void
    {
        $member->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function updateMemberSeriesStatus(Member $member, int $seriesId, int $status): MemberSeriesStatus
    {
        return MemberSeriesStatus::updateOrCreate(
            [
                'member_id' => $member->id,
                'series_id' => $seriesId,
            ],
            [
                'status' => $status,
            ]
        );
    }
}
