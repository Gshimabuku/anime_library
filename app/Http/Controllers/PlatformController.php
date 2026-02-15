<?php

namespace App\Http\Controllers;

use App\Http\Requests\Platform\IndexPlatformRequest;
use App\Http\Requests\Platform\StorePlatformRequest;
use App\Http\Requests\Platform\UpdatePlatformRequest;
use App\Models\Platform;
use App\Services\PlatformService;

class PlatformController extends Controller
{
    public function __construct(
        private readonly PlatformService $platformService
    ) {}

    public function index(IndexPlatformRequest $request)
    {
        $searchParams = $request->getSearchParams();
        $platforms = $this->platformService->getPlatforms($searchParams);

        return view('platforms.index', compact('platforms', 'searchParams'));
    }

    public function show(Platform $platform)
    {
        return view('platforms.show', compact('platform'));
    }

    public function create()
    {
        return view('platforms.form', ['platform' => new Platform()]);
    }

    public function store(StorePlatformRequest $request)
    {
        $this->platformService->createPlatform($request->validated());

        return redirect()->route('platforms.index')
            ->with('success', 'プラットフォームを追加しました。');
    }

    public function edit(Platform $platform)
    {
        return view('platforms.form', compact('platform'));
    }

    public function update(UpdatePlatformRequest $request, Platform $platform)
    {
        $this->platformService->updatePlatform($platform, $request->validated());

        return redirect()->route('platforms.show', $platform)
            ->with('success', 'プラットフォーム情報を更新しました。');
    }

    public function destroy(Platform $platform)
    {
        try {
            $this->platformService->deletePlatform($platform);
        } catch (\Exception $e) {
            return redirect()->route('platforms.show', $platform)
                ->with('error', 'このプラットフォームは作品に紐付いているため削除できません。');
        }

        return redirect()->route('platforms.index')
            ->with('success', 'プラットフォームを削除しました。');
    }
}
