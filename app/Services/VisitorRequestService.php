<?php 

// app/Services/SecurityMetricService.php
namespace App\Services;

use App\Models\VisitorRequest;
use App\Repositories\SecurityMetricRepository;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class VisitorRequestService
{
    public function list(?string $month = null): Collection
    {
        $query = VisitorRequest::with('host')->latest();

        if (! empty($month)) {
            // validate & parse month format YYYY-MM
            try {
                $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $end   = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
                $query->whereBetween('visit_date', [$start, $end]);
            } catch (\Throwable $e) {
                // jika format salah, kembalikan empty collection (controller bisa menanggapinya)
                return collect();
            }
        }

        return $query->get();
    }

    public function store(array $data)
    {
        return VisitorRequest::create($data);
    }

    public function approve(VisitorRequest $visitorRequest, User $user): VisitorRequest
    {
        $isHost = $user->id === $visitorRequest->host_id;
        $isReceptionist = method_exists($user, 'hasRole') ? $user->hasRole('receptionist') : false;

        if (! $isHost && ! $isReceptionist) {
            throw new \Exception('Only the host or receptionist can approve this request.');
        }

        $visitorRequest->update([
            'status' => 'approved',
        ]);

        return $visitorRequest->fresh();
    }

    public function reject(VisitorRequest $visitorRequest, User $user, ?string $notes = null): VisitorRequest
    {
        $isHost = $user->id === $visitorRequest->host_id;
        $isReceptionist = method_exists($user, 'hasRole') ? $user->hasRole('receptionist') : false;

        if (! $isHost && ! $isReceptionist) {
            throw new \Exception('Only the host or receptionist can reject this request.');
        }

        $visitorRequest->update([
            'status' => 'rejected',
            'notes'  => $notes,
        ]);

        return $visitorRequest->fresh();
    }

    public function analytics(?string $month = null): array
    {
        $query = VisitorRequest::query();

        if ($month) {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $end   = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
            $query->whereBetween('visit_date', [$start, $end]);
        }

        $total = (clone $query)->count();

        $pending   = (clone $query)->where('status', 'pending')->count();
        $approved  = (clone $query)->where('status', 'approved')->count();
        $onsite    = (clone $query)->where('status', 'onsite')->count();
        $completed = (clone $query)->where('status', 'completed')->count();

        // active = approved + onsite
        $active = $approved + $onsite;

        // growth dibanding bulan lalu
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd   = Carbon::now()->subMonth()->endOfMonth();
        $lastMonth      = VisitorRequest::whereBetween('visit_date', [$lastMonthStart, $lastMonthEnd])->count();

        $growth = $lastMonth > 0
            ? round((($total - $lastMonth) / $lastMonth) * 100, 2)
            : 100;

        return [
            'month'      => $month ?? Carbon::now()->format('Y-m'),
            'total'      => $total,
            'pending'    => $pending,
            'approved'   => $approved,
            'onsite'     => $onsite,
            'completed'  => $completed,
            'active'     => $active,
            'last_month' => $lastMonth,
            'growth'     => $growth,
        ];
    }

}
