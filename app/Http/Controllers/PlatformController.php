<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');

        $platforms = Platform::search($keyword)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        return view('platforms.index', compact('platforms', 'keyword'));
    }

    public function show(Platform $platform)
    {
        return view('platforms.show', compact('platform'));
    }

    public function create()
    {
        return view('platforms.form', ['platform' => new Platform()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:platforms,name',
            'is_active' => 'required|boolean',
        ]);

        $maxOrder = Platform::max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;

        Platform::create($validated);

        return redirect()->route('platforms.index')
            ->with('success', 'プラットフォームを追加しました。');
    }

    public function edit(Platform $platform)
    {
        return view('platforms.form', compact('platform'));
    }

    public function update(Request $request, Platform $platform)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:platforms,name,' . $platform->id,
            'is_active' => 'required|boolean',
        ]);

        $platform->update($validated);

        return redirect()->route('platforms.show', $platform)
            ->with('success', 'プラットフォーム情報を更新しました。');
    }

    public function destroy(Platform $platform)
    {
        // 紐付きがある場合は削除不可（restrictOnDelete）
        try {
            $platform->delete();
        } catch (\Exception $e) {
            return redirect()->route('platforms.show', $platform)
                ->with('error', 'このプラットフォームは作品に紐付いているため削除できません。');
        }

        return redirect()->route('platforms.index')
            ->with('success', 'プラットフォームを削除しました。');
    }
}
