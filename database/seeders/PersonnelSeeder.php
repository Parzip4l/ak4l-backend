<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Security\Personnel;

class PersonnelSeeder extends Seeder
{
    public function run(): void
    {
        $personnels = [
            [
                'name' => 'Ahmad Santoso',
                'job_position_id' => '1',
                'bujp' => 'PT Garda Nusantara',
                'kta_number' => 'KTA-001',
                'code' => 'SEC-001',
            ],
            [
                'name' => 'Budi Hartono',
                'job_position_id' => '1',
                'bujp' => 'PT Garda Nusantara',
                'kta_number' => 'KTA-002',
                'code' => 'SEC-002',
            ],
            [
                'name' => 'Citra Dewi',
                'job_position_id' => '2',
                'bujp' => 'PT Bhakti Jaya',
                'kta_number' => 'KTA-003',
                'code' => 'SEC-003',
            ],
            [
                'name' => 'Dedi Kusuma',
                'job_position_id' => '1',
                'bujp' => 'PT Bhakti Jaya',
                'kta_number' => 'KTA-004',
                'code' => 'SEC-004',
            ],
        ];

        foreach ($personnels as $p) {
            Personnel::firstOrCreate(['code' => $p['code']], $p);
        }
    }
}
