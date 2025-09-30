<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Security\PersonnelSkill;
use App\Models\Security\Personnel;
use App\Models\Security\Skill;

class PersonnelSkillSeeder extends Seeder
{
    public function run(): void
    {
        $assignments = [
            ['personnel_code' => 'SEC-001', 'skill_name' => 'Basic Security Training', 'status' => 'approved'],
            ['personnel_code' => 'SEC-001', 'skill_name' => 'Handling Fire Emergency', 'status' => 'pending'],
            ['personnel_code' => 'SEC-002', 'skill_name' => 'Basic Security Training', 'status' => 'approved'],
            ['personnel_code' => 'SEC-002', 'skill_name' => 'CCTV Monitoring', 'status' => 'approved'],
            ['personnel_code' => 'SEC-003', 'skill_name' => 'First Aid / P3K', 'status' => 'pending'],
            ['personnel_code' => 'SEC-004', 'skill_name' => 'Defensive Driving', 'status' => 'approved'],
        ];

        foreach ($assignments as $a) {
            $personnel = Personnel::where('code', $a['personnel_code'])->first();
            $skill = Skill::where('name', $a['skill_name'])->first();

            if ($personnel && $skill) {
                PersonnelSkill::firstOrCreate(
                    [
                        'personnel_id' => $personnel->id,
                        'skill_id' => $skill->id,
                    ],
                    [
                        'status' => $a['status'],
                        'certificate_file' => null,
                        'membership_card' => null,
                    ]
                );
            }
        }
    }
}
