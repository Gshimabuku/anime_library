<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Pagination\LengthAwarePaginator;

interface MemberService
{
    /**
     * メンバー一覧を取得する（検索・ページネーション付き）
     */
    public function getMembers(?string $keyword): LengthAwarePaginator;

    /**
     * メンバーを新規作成する
     */
    public function createMember(array $data): Member;

    /**
     * メンバー情報を更新する
     */
    public function updateMember(Member $member, array $data): Member;

    /**
     * メンバーを削除する
     */
    public function deleteMember(Member $member): void;
}
