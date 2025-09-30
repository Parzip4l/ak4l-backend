<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserRoleController;
use App\Http\Controllers\Api\V1\RolePermissionController;
use App\Http\Controllers\Api\V1\SafetyMetricController;
use App\Http\Controllers\Api\V1\MedicalReportController;
use App\Http\Controllers\Api\V1\RikesAttendanceController;
use App\Http\Controllers\Api\V1\SecurityMetricController;
use App\Http\Controllers\Api\V1\IncidentCategoryController;
use App\Http\Controllers\Api\V1\VisitorRequestController;
use App\Http\Controllers\Api\V1\SecurityKeyMetricController;
use App\Http\Controllers\Api\V1\RikesNapzaController;
use App\Http\Controllers\Api\V1\RikesPradinasController;
use App\Http\Controllers\Api\V1\MedicalOnsiteReportController;
use App\Http\Controllers\Api\V1\BUJPReportController;
use App\Http\Controllers\Api\V1\Security\{
    SkillController,
    PersonnelController,
    PersonnelSkillController,
    JobPositionController
};

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        // Permission and Roles
            // Role
            Route::get('roles', [RolePermissionController::class, 'roles']);
            Route::post('roles', [RolePermissionController::class, 'storeRole']);
            Route::put('roles/{id}', [RolePermissionController::class, 'updateRole']);
            Route::delete('roles/{id}', [RolePermissionController::class, 'destroyRole']);

            // Permission
            Route::get('permissions', [RolePermissionController::class, 'permissions']);
            Route::post('permissions', [RolePermissionController::class, 'storePermission']);
            Route::put('permissions/{id}', [RolePermissionController::class, 'updatePermission']);
            Route::delete('permissions/{id}', [RolePermissionController::class, 'destroyPermission']);

            // Assign / Revoke Permission ke Role
            Route::post('roles/{roleId}/assign-permissions', [RolePermissionController::class, 'assignPermissionToRole']);
            Route::post('roles/{roleId}/revoke-permissions', [RolePermissionController::class, 'revokePermissionFromRole']);

        Route::post('users/{id}/roles/assign', [UserRoleController::class, 'assignRole']);
        Route::post('users/{id}/roles/revoke', [UserRoleController::class, 'revokeRole']);
        Route::post('/users/{user}/assign-permission', [UserRoleController::class, 'assignPermission']);
        Route::post('/users/{user}/remove-permission', [UserRoleController::class, 'removePermission']);

        // Safety Metrics
        Route::get('/safety-metrics', [SafetyMetricController::class, 'index'])
        ->middleware('permission:safety_metrics.read');

        Route::get('/safety-metrics/{safetyMetric}', [SafetyMetricController::class, 'show'])
            ->middleware('permission:safety_metrics.read');

        Route::post('/safety-metrics', [SafetyMetricController::class, 'store'])
            ->middleware('permission:safety_metrics.create');

        Route::put('/safety-metrics/{safetyMetric}', [SafetyMetricController::class, 'update'])
            ->middleware('permission:safety_metrics.update');

        Route::delete('/safety-metrics/{safetyMetric}', [SafetyMetricController::class, 'destroy'])
            ->middleware('permission:safety_metrics.delete');
        
        Route::get('safety-metrics/summary/monthly', [SafetyMetricController::class, 'monthlySummary'])
        ->middleware('permission:safety_metrics.read');

        Route::get('latest-safety-metric', [SafetyMetricController::class, 'latest'])
        ->middleware('permission:safety_metrics.read');

        Route::get('latest-by-month', [SafetyMetricController::class, 'latestByMonth'])
        ->middleware('permission:safety_metrics.read');


        // Nafza
        Route::get('medical-reports', [MedicalReportController::class, 'index'])
        ->middleware('permission:medical_reports.read');

        Route::post('medical-reports', [MedicalReportController::class, 'store'])
            ->middleware('permission:medical_reports.create');

        Route::put('medical-reports/{medicalReport}', [MedicalReportController::class, 'update'])
        ->middleware('permission:medical_reports.create');

        Route::get('medical-reports/{medicalReport}/single', [MedicalReportController::class, 'show'])
            ->middleware('permission:medical_reports.read');

        Route::post('medical-reports/{medicalReport}/approve', [MedicalReportController::class, 'approve'])
            ->middleware('permission:medical_reports.approve');

        Route::post('medical-reports/{medicalReport}/reject', [MedicalReportController::class, 'reject'])
            ->middleware('permission:medical_reports.approve');

        Route::get('medical-reports/filter', [MedicalReportController::class, 'filter'])
            ->middleware('permission:medical_reports.read');

        Route::get('medical-reports/range', [MedicalReportController::class, 'range'])
            ->middleware('permission:medical_reports.read');
                // v2
                Route::prefix('rikes-napza')->group(function () {
                    Route::get('/', [RikesNapzaController::class, 'index'])->middleware('permission:medical_reports.read');
                    Route::post('/', [RikesNapzaController::class, 'store'])->middleware('permission:medical_reports.create');
                    Route::get('/{rikesNapza}', [RikesNapzaController::class, 'show'])->middleware('permission:medical_reports.read');
                    Route::put('/{rikesNapza}', [RikesNapzaController::class, 'update'])->middleware('permission:medical_reports.update');
                    Route::delete('/{rikesNapza}', [RikesNapzaController::class, 'destroy'])->middleware('permission:medical_reports.delete');

                    // analytics filter
                    Route::get('/filter/month', [RikesNapzaController::class, 'filterByMonth'])->middleware('permission:medical_reports.read');
                    Route::get('/filter/year', [RikesNapzaController::class, 'filterByYear'])->middleware('permission:medical_reports.read');
                });

                // Pradinas
                Route::prefix('rikes-pradinas')->group(function () {
                    Route::get('/', [RikesPradinasController::class, 'index'])->middleware('permission:medical_reports.read');              
                    Route::post('/', [RikesPradinasController::class, 'store'])->middleware('permission:medical_reports.create');             
                    Route::get('/{rikesPradinas}', [RikesPradinasController::class, 'show'])->middleware('permission:medical_reports.read'); 
                    Route::put('/{rikesPradinas}', [RikesPradinasController::class, 'update'])->middleware('permission:medical_reports.update');
                    Route::delete('/{rikesPradinas}', [RikesPradinasController::class, 'destroy'])->middleware('permission:medical_reports.delete');

                    // filter by bulan & tahun
                    Route::get('/filter/month', [RikesPradinasController::class, 'filterByMonth'])->middleware('permission:medical_reports.read');;
                    Route::get('/filter/year', [RikesPradinasController::class, 'filterByYear'])->middleware('permission:medical_reports.read');
                });

                // Medical Report Onsite
                Route::prefix('medical-reports-onsite')->group(function () {
                    Route::get('/', [MedicalOnsiteReportController::class, 'index'])
                        ->middleware('permission:medical_reports.read');

                    // letakkan di atas
                    Route::get('/filter', [MedicalOnsiteReportController::class, 'filterData'])
                        ->middleware('permission:medical_reports.read');

                    Route::get('/recap', [MedicalOnsiteReportController::class, 'recap'])
                        ->middleware('permission:medical_reports.read');

                    Route::get('/monthly-trend', [MedicalOnsiteReportController::class, 'monthlyTrend'])
                        ->middleware('permission:medical_reports.read');

                    // detail pakai ID
                    Route::get('/{report}', [MedicalOnsiteReportController::class, 'show'])
                        ->middleware('permission:medical_reports.read');

                    Route::post('/', [MedicalOnsiteReportController::class, 'store'])
                        ->middleware('permission:medical_reports.create');

                    Route::put('/{report}', [MedicalOnsiteReportController::class, 'update'])
                        ->middleware('permission:medical_reports.update');

                    Route::delete('/{report}', [MedicalOnsiteReportController::class, 'destroy'])
                        ->middleware('permission:medical_reports.delete');

                    Route::get('/{report}/logs', [MedicalOnsiteReportController::class, 'approvalLogs'])
                        ->middleware('permission:medical_reports.read');

                    Route::post('/{report}/approve', [MedicalOnsiteReportController::class, 'approve'])
                        ->middleware('permission:medical_reports.approve');
                });


        // BUJP
        Route::prefix('bujp-reports')->group(function () {
            Route::get('/', [BUJPReportController::class, 'index'])->middleware('permission:security_metrics.read');
            Route::post('/', [BUJPReportController::class, 'store'])->middleware('permission:security_metrics.create');
            Route::get('/filter', [BUJPReportController::class, 'filter'])
                    ->middleware('permission:security_metrics.read');
            Route::post('{bujpReport}/approve', [BUJPReportController::class, 'approve'])->middleware('permission:security_metrics.create');
            Route::post('{bujpReport}/reject', [BUJPReportController::class, 'reject'])->middleware('permission:security_metrics.create');
            Route::get('{bujpReport}', [BUJPReportController::class, 'show'])->middleware('permission:security_metrics.read');
            Route::get('{bujpReport}/logs', [BUJPReportController::class, 'approvalLogs'])->middleware('permission:security_metrics.read');
            Route::get('{bujpReport}/download', [BUJPReportController::class, 'download'])->middleware('permission:security_metrics.read');
            
        });

        // Rikes Attendance
        Route::get('rikes-attendances', [RikesAttendanceController::class, 'index'])
        ->middleware('permission:medical_reports.read');

        Route::post('rikes-attendances', [RikesAttendanceController::class, 'store'])
            ->middleware('permission:medical_reports.create');

        Route::put('rikes-attendances/{attendance}', [RikesAttendanceController::class, 'update'])
            ->middleware('permission:medical_reports.update');

        Route::delete('rikes-attendances/{attendance}', [RikesAttendanceController::class, 'destroy'])
            ->middleware('permission:medical_reports.delete');

        Route::get('rikes-attendance/recap', [RikesAttendanceController::class, 'recap'])
        ->middleware('permission:medical_reports.read');

        //Metriks Security
        Route::get('security-metrics', [SecurityMetricController::class, 'index'])
        ->middleware('permission:security_metrics.read');

        Route::post('security-metrics', [SecurityMetricController::class, 'store'])
            ->middleware('permission:security_metrics.create');
            
        Route::get('security-metrics/{securityMetric}', [SecurityMetricController::class, 'show'])
            ->middleware('permission:security_metrics.read');
            
        Route::put('security-metrics/{securityMetric}', [SecurityMetricController::class, 'update'])
            ->middleware('permission:security_metrics.update');

        Route::delete('security-metrics/{securityMetric}', [SecurityMetricController::class, 'destroy'])
            ->middleware('permission:security_metrics.delete');

        Route::post('security-metrics/{securityMetric}/approve', [SecurityMetricController::class, 'approve'])
            ->middleware('permission:security_metrics.update');

        Route::post('security-metrics/{securityMetric}/reject', [SecurityMetricController::class, 'reject'])
            ->middleware('permission:security_metrics.update'); 

        // Metrik v2 security
        Route::get('security-metrics-v2', [SecurityKeyMetricController::class, 'index'])
            ->middleware('permission:security_metrics.read');

        Route::post('security-metrics-v2', [SecurityKeyMetricController::class, 'store'])
            ->middleware('permission:security_metrics.create');

        Route::put('security-metrics-v2/{securityKeyMetric}', [SecurityKeyMetricController::class, 'update'])
            ->middleware('permission:security_metrics.update');

        Route::delete('security-metrics-v2/{securityKeyMetric}', [SecurityKeyMetricController::class, 'destroy'])
            ->middleware('permission:security_metrics.delete');

        // Route tambahan untuk analytics
        Route::get('security-metrics-v2-analytics', [SecurityKeyMetricController::class, 'analyticsMonthly'])
            ->middleware('permission:security_metrics.read');


        // Incident Categori
        Route::get('incident-categories', [IncidentCategoryController::class, 'index'])
            ->middleware('permission:security_metrics.read');
        Route::post('incident-categories', [IncidentCategoryController::class, 'store'])
            ->middleware('permission:security_metrics.create');
        Route::put('incident-categories/{incidentCategory}', [IncidentCategoryController::class, 'update'])
            ->middleware('permission:security_metrics.update');
        Route::delete('incident-categories/{incidentCategory}', [IncidentCategoryController::class, 'destroy'])
            ->middleware('permission:security_metrics.delete');

        // Visitor Management
        
        Route::get('visitor-requests', [VisitorRequestController::class, 'index'])
        ->middleware('permission:visitor_requests.read');

        Route::post('visitor-requests', [VisitorRequestController::class, 'store'])
            ->middleware('permission:visitor_requests.create');

        Route::post('visitor-requests/{visitorRequest}/approve', [VisitorRequestController::class, 'approve'])
            ->middleware('permission:visitor_requests.approve');

        Route::post('visitor-requests/{visitorRequest}/reject', [VisitorRequestController::class, 'reject'])
            ->middleware('permission:visitor_requests.approve');

        Route::get('visitor-requests/analytics', [VisitorRequestController::class, 'analytics'])
            ->middleware('permission:visitor_requests.read');

        Route::get('visitor-requests/summary/monthly', [VisitorRequestController::class, 'monthlySummary'])
            ->middleware('permission:visitor_requests.read');

        Route::get('visitor-requests/summary/top-hosts', [VisitorRequestController::class, 'topHosts'])
            ->middleware('permission:visitor_requests.read');

        Route::get('visitor-requests/active', [VisitorRequestController::class, 'activeToday'])
            ->middleware('permission:visitor_requests.read');

        Route::get('visitor-requests/export/csv', [VisitorRequestController::class, 'exportCsv'])
            ->middleware('permission:visitor_requests.read');
        
        Route::post('visitor-requests/{visitorRequest}/complete', [VisitorRequestController::class, 'complete'])
            ->middleware('permission:visitor_requests.approve');


        // Security Routes

        Route::prefix('skills')->group(function () {
            Route::get('/', [SkillController::class, 'index'])
                ->middleware('permission:security_metrics.read');
            Route::post('/', [SkillController::class, 'store'])
                ->middleware('permission:security_metrics.create');
            Route::get('/{id}', [SkillController::class, 'show'])
                ->middleware('permission:security_metrics.read');
            Route::put('/{id}', [SkillController::class, 'update'])
                ->middleware('permission:security_metrics.update');
            Route::delete('/{id}', [SkillController::class, 'destroy'])
                ->middleware('permission:security_metrics.delete');
                
        });

        // Job Position
        Route::prefix('job-positions')->group(function () {
            Route::get('/', [JobPositionController::class, 'index'])
                ->middleware('permission:security_metrics.read');
            Route::post('/', [JobPositionController::class, 'store'])
                ->middleware('permission:security_metrics.create');
            Route::get('/{id}', [JobPositionController::class, 'show'])
                ->middleware('permission:security_metrics.read');
            Route::put('/{id}', [JobPositionController::class, 'update'])
                ->middleware('permission:security_metrics.update');
            Route::delete('/{id}', [JobPositionController::class, 'destroy'])
                ->middleware('permission:security_metrics.delete');
        });

        // ==========================
        //  PERSONNEL ROUTES
        // ==========================
        Route::prefix('personnels')->group(function () {
            Route::get('/', [PersonnelController::class, 'index'])
                ->middleware('permission:security_metrics.read');
            Route::post('/', [PersonnelController::class, 'store'])
                ->middleware('permission:security_metrics.create');
            Route::get('/analytics', [PersonnelController::class, 'analytics'])
                ->middleware('permission:security_metrics.read');
            Route::get('/{id}', [PersonnelController::class, 'show'])
                ->middleware('permission:security_metrics.read');
            Route::put('/{id}', [PersonnelController::class, 'update'])
                ->middleware('permission:security_metrics.update');
            Route::delete('/{id}', [PersonnelController::class, 'destroy'])
                ->middleware('permission:security_metrics.delete');
           
        });

        // ==========================
        //  PERSONNEL SKILL ROUTES
        // ==========================
        Route::prefix('personnel-skills')->group(function () {
            // List with filters (month, year, status)
            Route::get('/', [PersonnelSkillController::class, 'index'])
                ->middleware('permission:security_metrics.read');

            // Assign skill ke personnel
            Route::post('/', [PersonnelSkillController::class, 'store'])
                ->middleware('permission:security_metrics.create');

            // Detail + logs
            Route::get('/{id}', [PersonnelSkillController::class, 'show'])
                ->middleware('permission:security_metrics.read');

            // Approve / Reject
            Route::post('/{id}/status', [PersonnelSkillController::class, 'updateStatus'])
                ->middleware('permission:security_metrics.approve');

            // Analytics (approved vs pending)
            Route::get('/analytics', [PersonnelSkillController::class, 'analytics'])
                ->middleware('permission:security_metrics.read');

            // Download file (certificate or membership_card)
            Route::get('/{id}/download/{type}', [PersonnelSkillController::class, 'downloadFile'])
                ->middleware('permission:security_metrics.read');
        });
        
        Route::get('/reports/pending', [\App\Http\Controllers\Api\V1\ReportSummaryController::class, 'pending']);

    });
});