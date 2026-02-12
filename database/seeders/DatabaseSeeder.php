<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // seederファイルリスト
        $tables = [
            MemberSeeder::class,
            AnimeTitleSeeder::class,
            SeriesSeeder::class,
            EpisodeSeeder::class,
            PlatformSeeder::class,
            SeriesPlatformAvailabilitySeeder::class,
            MemberSeriesStatusSeeder::class,
        ];

        // seeder実行
        $this->call($tables);
    }
}
