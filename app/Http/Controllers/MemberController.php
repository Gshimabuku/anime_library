<?php

namespace App\Http\Controllers;

use App\Http\Requests\Member\StoreMemberRequest;
use App\Http\Requests\Member\UpdateMemberRequest;
use App\Models\Member;
use App\Services\MemberService;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(
        private readonly MemberService $memberService
    ) {}

    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $members = $this->memberService->getMembers($keyword);

        return view('members.index', compact('members', 'keyword'));
    }

    public function show(Member $member)
    {
        return view('members.show', compact('member'));
    }

    public function watchStatus(Member $member)
    {
        // 全作品を取得（シリーズ情報も含む）
        $animeTitles = \App\Models\AnimeTitle::with(['series' => function ($query) {
            $query->orderBy('series_order');
        }, 'series.episodes'])
        ->orderBy('id')
        ->get();

        // 全シリーズIDを取得
        $allSeriesIds = [];
        foreach ($animeTitles as $animeTitle) {
            foreach ($animeTitle->series as $series) {
                $allSeriesIds[] = $series->id;
            }
        }

        // このメンバーの視聴状況を取得
        $watchStatuses = \App\Models\MemberSeriesStatus::where('member_id', $member->id)
            ->whereIn('series_id', $allSeriesIds)
            ->get()
            ->groupBy('series_id');

        return view('members.watch-status', compact('member', 'animeTitles', 'watchStatuses'));
    }

    public function create()
    {
        return view('members.form', ['member' => new Member()]);
    }

    public function store(StoreMemberRequest $request)
    {
        $this->memberService->createMember($request->validated());

        return redirect()->route('members.index')
            ->with('success', 'メンバーを追加しました。');
    }

    public function edit(Member $member)
    {
        return view('members.form', compact('member'));
    }

    public function update(UpdateMemberRequest $request, Member $member)
    {
        $this->memberService->updateMember($member, $request->validated());

        return redirect()->route('members.show', $member)
            ->with('success', 'メンバー情報を更新しました。');
    }

    public function destroy(Member $member)
    {
        $this->memberService->deleteMember($member);

        return redirect()->route('members.index')
            ->with('success', 'メンバーを削除しました。');
    }
}
