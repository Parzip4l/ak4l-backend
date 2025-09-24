<?php 

// database/seeders/IncidentCategorySeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IncidentCategory;

class IncidentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Pencurian', 'description' => 'Kejadian pencurian di area proyek/operasional'],
            ['name' => 'Ancaman Bom', 'description' => 'Laporan terkait ancaman bom'],
            ['name' => 'Vandalisme', 'description' => 'Kerusakan fasilitas akibat tindakan vandalisme'],
            ['name' => 'Audit Internal', 'description' => 'Audit rutin atau temuan audit'],
            ['name' => 'Simulasi Keamanan', 'description' => 'Kegiatan simulasi keamanan / evakuasi'],
        ];

        foreach ($categories as $cat) {
            IncidentCategory::firstOrCreate(
                ['name' => $cat['name']],
                ['description' => $cat['description']]
            );
        }
    }
}
