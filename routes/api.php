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

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        // Permission and Roles
        Route::get('roles', [RolePermissionController::class, 'roles']);
        Route::get('permissions', [RolePermissionController::class, 'permissions']);

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

    });
});