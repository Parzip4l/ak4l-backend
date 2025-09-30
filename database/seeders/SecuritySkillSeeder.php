<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Security\Skill;

class SecuritySkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            [
                'name' => 'Basic Security Training',
                'category' => 'Training',
                'criteria' => 'Wajib untuk semua personel BUJP',
                'reference' => 'SK Menaker No. 123/2022',
            ],
            [
                'name' => 'Handling Fire Emergency',
                'category' => 'Emergency',
                'criteria' => 'Minimal 1 orang per pos',
                'reference' => 'Peraturan Damkar DKI',
            ],
            [
                'name' => 'First Aid / P3K',
                'category' => 'Medical',
                'criteria' => 'Minimal 10% dari total personel',
                'reference' => 'Permenaker No. 15/2008',
            ],
            [
                'name' => 'Crowd Control',
                'category' => 'Operational',
                'criteria' => 'Wajib untuk event / keramaian',
                'reference' => 'SK Kapolri No. 45/2021',
            ],
            [
                'name' => 'CCTV Monitoring',
                'category' => 'Technology',
                'criteria' => 'Harus memahami SOP monitoring',
                'reference' => 'Internal SOP Security',
            ],
            [
                'name' => 'Defensive Driving',
                'category' => 'Transport',
                'criteria' => 'Wajib untuk pengemudi operasional',
                'reference' => 'Peraturan LLAJ 2020',
            ],
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(
                ['name' => $skill['name']],
                $skill
            );
        }
    }
}
