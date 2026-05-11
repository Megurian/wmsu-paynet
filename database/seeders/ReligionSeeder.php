<?php

namespace Database\Seeders;

use App\Models\Religion;
use Illuminate\Database\Seeder;

class ReligionSeeder extends Seeder
{
    public function run(): void
    {
        $religions = [
            'Roman Catholic',
            'Iglesia Ni Cristo',
            'Islam',
            'Born Again Christian',
            'Protestant',
            'Pentecostal',
            'Apostolic',
            'Assemblies of God',
            'Church of God',
            'Church of Christ',
            'Christian and Missionary Alliance',
            'Seventh-day Adventist',
            'Baptist',
            'Bible Baptist',
            'Methodist',
            'Jehovah\'s Witness',
            'The Church of Jesus Christ of Latter-day Saints',
            'Philippine Independent Church',
            'United Church of Christ in the Philippines',
        ];

        foreach ($religions as $name) {
            Religion::updateOrCreate(
                ['name' => $name],
                []
            );
        }
    }
}