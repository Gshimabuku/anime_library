<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');

        $members = Member::search($keyword)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        return view('members.index', compact('members', 'keyword'));
    }

    public function show(Member $member)
    {
        return view('members.show', compact('member'));
    }

    public function create()
    {
        return view('members.form', ['member' => new Member()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'is_active' => 'required|boolean',
        ]);

        $maxOrder = Member::max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;

        Member::create($validated);

        return redirect()->route('members.index')
            ->with('success', 'メンバーを追加しました。');
    }

    public function edit(Member $member)
    {
        return view('members.form', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'is_active' => 'required|boolean',
        ]);

        $member->update($validated);

        return redirect()->route('members.show', $member)
            ->with('success', 'メンバー情報を更新しました。');
    }

    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()->route('members.index')
            ->with('success', 'メンバーを削除しました。');
    }
}
