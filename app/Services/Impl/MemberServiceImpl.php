<?php

namespace App\Services\Impl;

use App\Models\Member;
use App\Models\MemberSeriesStatus;
use App\Services\MemberService;
use Illuminate\Pagination\LengthAwarePaginator;

class MemberServiceImpl implements MemberService
{
    /**
     * {@inheritdoc}
     */
    public function getMembers(?string $keyword): LengthAwarePaginator
    {
        return Member::query()
            ->addSelect([
                'watched_anime_titles_count' => MemberSeriesStatus::query()
                    ->join('series', 'member_series_statuses.series_id', '=', 'series.id')
                    ->whereColumn('member_series_statuses.member_id', 'members.id')
                    ->where('member_series_statuses.status', \App\Enums\WatchStatus::WATCHED->value)
                    ->selectRaw('COUNT(DISTINCT series.anime_title_id)')
            ])
            ->when($keyword, fn ($q) => $q->where('name', 'like', "%{$keyword}%"))
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
}
