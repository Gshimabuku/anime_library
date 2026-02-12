<?php

namespace App\Http\Controllers;

use App\Models\AnimeTitle;
use App\Models\Member;
use App\Models\Platform;

class DashboardController extends Controller
{
    public function index()
    {
        $memberCount = Member::count();
        $titleCount = AnimeTitle::count();
        $platformCount = Platform::count();

        return view('dashboard', compact('memberCount', 'titleCount', 'platformCount'));
    }
}
