<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {

        // Permissions CRUD dasar
        $permissions = [
            'safety_metrics.read',
            'safety_metrics.create',
            'safety_metrics.update',
            'safety_metrics.delete',
            'safety_metrics.approve',

            'medical_reports.read',
            'medical_reports.create',
            'medical_reports.update',
            'medical_reports.delete',
            'medical_reports.approve',

            'security_metrics.read',
            'security_metrics.create',
            'security_metrics.update',
            'security_metrics.delete',

            'visitor_requests.read',
            'visitor_requests.create',
            'visitor_requests.approve',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'api',   // 🔑
            ]);
        }

        // Roles
        $roles = [
            'admin',
            'qshe-admin',
            'security-admin',
            'medical-admin',
            'viewer',
            'receptionist',
        ];

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'api', 
            ]);

            switch ($roleName) {
                case 'admin':
                    $role->syncPermissions(Permission::all());
                    break;

                case 'qshe-admin':
                    $role->syncPermissions([
                        'safety_metrics.read',
                        'safety_metrics.create',
                        'safety_metrics.update',
                        'safety_metrics.delete',
                        'safety_metrics.approve',

                        'medical_reports.read',
                        'medical_reports.create',
                        'medical_reports.update',
                        'medical_reports.delete',
                        'medical_reports.approve',
                    ]);
                    break;

                case 'security-admin':
                    $role->syncPermissions([
                        'security_metrics.read',
                        'security_metrics.create',
                        'security_metrics.update',
                        'security_metrics.delete',

                        'visitor_requests.read',
                        'visitor_requests.create',
                        'visitor_requests.approve',
                    ]);
                    break;

                case 'medical-admin':
                    $role->syncPermissions([
                        'medical_reports.read',
                        'medical_reports.create',
                        'medical_reports.update',
                        'medical_reports.delete',
                        'medical_reports.approve',
                    ]);
                    break;

                case 'viewer':
                    $role->syncPermissions([
                        'safety_metrics.read',
                        'medical_reports.read',
                        'security_metrics.read',
                        'visitor_requests.read',
                    ]);
                    break;

                case 'receptionist':
                    $role->syncPermissions([
                        'visitor_requests.read',
                        'visitor_requests.create',
                        'visitor_requests.approve',
                    ]);
                    break;
            }
        }
    }
}
