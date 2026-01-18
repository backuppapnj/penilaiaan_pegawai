<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Employee;
use App\Models\Period;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics based on user role.
     */
    public function getStats(Request $request): Response
    {
        $user = Auth::user();
        $role = $user->role;

        $stats = match ($role) {
            'SuperAdmin' => $this->getSuperAdminStats(),
            'Admin' => $this->getAdminStats(),
            'Penilai' => $this->getPenilaiStatsData($user),
            'Peserta' => $this->getPesertaStatsData($user),
            default => [],
        };

        $activityLogs = $role === 'SuperAdmin' ? $this->getActivity($request) : [];

        return match ($role) {
            'SuperAdmin' => Inertia::render('Dashboard/SuperAdmin/Index', [
                'stats' => $stats,
                'activityLogs' => $activityLogs,
            ]),
            'Admin' => Inertia::render('Dashboard/Admin/Index', [
                'stats' => $stats,
            ]),
            'Penilai' => Inertia::render('Dashboard/Penilai/Index', [
                'stats' => $stats,
            ]),
            'Peserta' => Inertia::render('Dashboard/Peserta/Index', [
                'stats' => $stats,
            ]),
            default => Inertia::render('dashboard'),
        };
    }

    /**
     * Get Penilai dashboard stats.
     */
    public function getPenilaiStats(Request $request): Response
    {
        $stats = $this->getPenilaiStatsData(Auth::user());

        return Inertia::render('Dashboard/Penilai/Index', [
            'stats' => $stats,
        ]);
    }

    /**
     * Get Peserta dashboard stats.
     */
    public function getPesertaStats(Request $request): Response
    {
        $stats = $this->getPesertaStatsData(Auth::user());

        return Inertia::render('Dashboard/Peserta/Index', [
            'stats' => $stats,
        ]);
    }

    /**
     * Get dashboard statistics based on user role (API).
     */
    public function getStatsApi(Request $request): array
    {
        $user = Auth::user();
        $role = $user->role;

        return match ($role) {
            'SuperAdmin' => $this->getSuperAdminStats(),
            'Admin' => $this->getAdminStats(),
            'Penilai' => $this->getPenilaiStatsData($user),
            'Peserta' => $this->getPesertaStatsData($user),
            default => [],
        };
    }

    /**
     * Get recent activity logs.
     */
    public function getActivity(Request $request): array
    {
        $logs = AuditLog::with('user')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'user' => $log->user?->name ?? 'System',
                'created_at' => $log->created_at->diffForHumans(),
            ]);

        return is_array($logs) ? $logs : $logs->toArray();
    }

    /**
     * Get voting progress for penilai.
     */
    public function getVotingProgress(Request $request): array
    {
        $user = Auth::user();
        $employeeId = $user?->employee?->id;
        $activePeriod = Period::where('status', 'open')->first();

        if (! $activePeriod) {
            return [
                'active_period' => null,
                'category_1' => ['completed' => 0, 'total' => 0, 'status' => 'pending'],
                'category_2' => ['completed' => 0, 'total' => 0, 'status' => 'pending'],
            ];
        }

        $categories = Category::with(['employees' => fn ($q) => $q
            ->when($employeeId, fn ($query) => $query->where('id', '!=', $employeeId))])
            ->whereIn('id', [1, 2])
            ->get();

        $progress = [];
        foreach ($categories as $category) {
            $totalEmployees = $category->employees->count();
            $votedCount = Vote::where('voter_id', $user->id)
                ->where('period_id', $activePeriod->id)
                ->where('category_id', $category->id)
                ->count();

            $progress["category_{$category->id}"] = [
                'completed' => $votedCount,
                'total' => $totalEmployees,
                'status' => $votedCount >= $totalEmployees && $totalEmployees > 0 ? 'completed' : 'in_progress',
            ];
        }

        return [
            'active_period' => [
                'id' => $activePeriod->id,
                'name' => $activePeriod->name,
                'start_date' => $activePeriod->start_date->format('d M Y'),
                'end_date' => $activePeriod->end_date->format('d M Y'),
            ],
            ...$progress,
        ];
    }

    /**
     * Get results for peserta when period is announced.
     */
    public function getResults(Request $request): array
    {
        $user = Auth::user();
        $announcedPeriod = Period::where('status', 'announced')
            ->latest()
            ->first();

        if (! $announcedPeriod) {
            return [
                'period' => null,
                'winners' => [],
                'my_rankings' => [],
                'my_certificates' => [],
            ];
        }

        $employee = $user->employee;

        // Get winners for each category
        $winners = Certificate::where('period_id', $announcedPeriod->id)
            ->where('rank', 1)
            ->with(['employee', 'category'])
            ->get()
            ->map(fn ($cert) => [
                'category' => $cert->category->nama,
                'employee_name' => $cert->employee->nama,
                'employee_nip' => $cert->employee->nip,
                'score' => $cert->score,
            ]);

        // Get my rankings if I'm an employee
        $myRankings = [];
        $myCertificates = [];

        if ($employee) {
            $myRankings = Certificate::where('period_id', $announcedPeriod->id)
                ->where('employee_id', $employee->id)
                ->with('category')
                ->get()
                ->map(fn ($cert) => [
                    'category' => $cert->category->nama,
                    'rank' => $cert->rank,
                    'score' => $cert->score,
                ]);

            $myCertificates = Certificate::where('employee_id', $employee->id)
                ->with(['period', 'category'])
                ->get()
                ->map(fn ($cert) => [
                    'id' => $cert->id,
                    'certificate_id' => $cert->certificate_id,
                    'period' => $cert->period->name,
                    'category' => $cert->category->nama,
                    'rank' => $cert->rank,
                    'issued_at' => $cert->issued_at->format('d M Y'),
                    'download_url' => route('peserta.certificates.download', $cert),
                ]);
        }

        return [
            'period' => [
                'id' => $announcedPeriod->id,
                'name' => $announcedPeriod->name,
                'year' => $announcedPeriod->year,
            ],
            'winners' => is_array($winners) ? $winners : $winners->toArray(),
            'my_rankings' => is_array($myRankings) ? $myRankings : $myRankings->toArray(),
            'my_certificates' => is_array($myCertificates) ? $myCertificates : $myCertificates->toArray(),
        ];
    }

    /**
     * Get SuperAdmin statistics.
     */
    protected function getSuperAdminStats(): array
    {
        $totalEmployees = Employee::count();
        $activePeriods = Period::where('status', 'open')->count();
        $totalVotes = Vote::count();
        $certificates = Certificate::count();

        $cat1Count = Employee::where('category_id', 1)->count();
        $cat2Count = Employee::where('category_id', 2)->count();

        return [
            'total_employees' => $totalEmployees,
            'active_periods' => $activePeriods,
            'total_votes' => $totalVotes,
            'certificates_generated' => $certificates,
            'category_1_count' => $cat1Count,
            'category_2_count' => $cat2Count,
        ];
    }

    /**
     * Get Admin statistics.
     */
    protected function getAdminStats(): array
    {
        $periods = Period::withCount('votes')
            ->latest()
            ->get()
            ->map(fn ($period) => [
                'id' => $period->id,
                'name' => $period->name,
                'status' => $period->status,
                'votes_count' => $period->votes_count,
                'start_date' => $period->start_date->format('d M Y'),
                'end_date' => $period->end_date->format('d M Y'),
            ]);

        $cat1Count = Employee::where('category_id', 1)->count();
        $cat2Count = Employee::where('category_id', 2)->count();

        $activePeriod = Period::where('status', 'open')->first();

        $votingProgress = [];
        if ($activePeriod) {
            $totalVoters = Employee::whereHas('user', fn ($q) => $q->whereIn('role', ['Penilai', 'Peserta', 'Admin', 'SuperAdmin']))->count();
            $votesCast = Vote::where('period_id', $activePeriod->id)->distinct('voter_id')->count('voter_id');

            $votingProgress = [
                'total_voters' => $totalVoters,
                'votes_cast' => $votesCast,
                'percentage' => $totalVoters > 0 ? round(($votesCast / $totalVoters) * 100, 1) : 0,
            ];
        }

        return [
            'periods' => is_array($periods) ? $periods : $periods->toArray(),
            'category_1_count' => $cat1Count,
            'category_2_count' => $cat2Count,
            'voting_progress' => $votingProgress,
            'has_active_period' => (bool) $activePeriod,
        ];
    }

    /**
     * Get Penilai statistics data.
     */
    protected function getPenilaiStatsData($user): array
    {
        $activePeriod = Period::where('status', 'open')->first();
        $employeeId = $user?->employee?->id;

        if (! $activePeriod) {
            return [
                'has_active_period' => false,
                'active_period' => null,
                'category_stats' => [],
                'recent_votes' => [],
            ];
        }

        $categories = Category::with(['employees' => fn ($q) => $q
            ->when($employeeId, fn ($query) => $query->where('id', '!=', $employeeId))])
            ->whereIn('id', [1, 2])
            ->get();

        $categoryStats = [];
        foreach ($categories as $category) {
            $totalEmployees = $category->employees->count();
            $votedCount = Vote::where('voter_id', $user->id)
                ->where('period_id', $activePeriod->id)
                ->where('category_id', $category->id)
                ->count();

            $categoryStats[] = [
                'id' => $category->id,
                'name' => $category->nama,
                'description' => $category->deskripsi,
                'completed' => $votedCount,
                'total' => $totalEmployees,
                'status' => $votedCount >= $totalEmployees && $totalEmployees > 0 ? 'completed' : 'pending',
                'percentage' => $totalEmployees > 0 ? round(($votedCount / $totalEmployees) * 100, 1) : 0,
            ];
        }

        $recentVotes = Vote::where('voter_id', $user->id)
            ->where('period_id', $activePeriod->id)
            ->with('employee')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($vote) => [
                'employee_name' => $vote->employee->nama,
                'category' => $vote->category->nama ?? '-',
                'total_score' => $vote->total_score,
                'voted_at' => $vote->voted_at->format('d M Y, H:i'),
            ]);

        return [
            'has_active_period' => true,
            'active_period' => $activePeriod ? [
                'id' => $activePeriod->id,
                'name' => $activePeriod->name,
                'start_date' => $activePeriod->start_date->format('d M Y'),
                'end_date' => $activePeriod->end_date->format('d M Y'),
            ] : null,
            'category_stats' => $categoryStats,
            'recent_votes' => is_array($recentVotes) ? $recentVotes : $recentVotes->toArray(),
        ];
    }

    /**
     * Get Peserta statistics data.
     */
    protected function getPesertaStatsData($user): array
    {
        $employee = $user->employee;
        $activePeriod = Period::where('status', 'open')->first();

        $announcedPeriod = Period::where('status', 'announced')
            ->latest()
            ->first();

        $profile = null;
        $myRankings = [];
        $myCertificates = [];

        if ($employee) {
            $categoryName = $employee->category?->nama ?? 'Tidak ada kategori';
            $profile = [
                'nama' => $employee->nama,
                'nip' => $employee->nip,
                'jabatan' => $employee->jabatan,
                'unit_kerja' => $employee->unit_kerja,
                'kategori' => $categoryName,
            ];

            if ($announcedPeriod) {
                $myRankings = $this->buildEmployeeRankings($announcedPeriod, $employee);
            }

            $myCertificates = Certificate::where('employee_id', $employee->id)
                ->with(['period', 'category'])
                ->latest('issued_at')
                ->limit(5)
                ->get()
                ->map(fn ($cert) => [
                    'id' => $cert->id,
                    'certificate_id' => $cert->certificate_id,
                    'period' => $cert->period->name,
                    'category' => $cert->category->nama,
                    'rank' => $cert->rank,
                    'issued_at' => $cert->issued_at->format('d M Y'),
                    'download_url' => route('peserta.certificates.download', $cert),
                ]);
        }

        return [
            'profile' => $profile,
            'has_active_period' => (bool) $activePeriod,
            'active_period' => $activePeriod ? [
                'id' => $activePeriod->id,
                'name' => $activePeriod->name,
                'start_date' => $activePeriod->start_date->format('d M Y'),
                'end_date' => $activePeriod->end_date->format('d M Y'),
            ] : null,
            'has_announced_period' => (bool) $announcedPeriod,
            'announced_period' => $announcedPeriod ? [
                'id' => $announcedPeriod->id,
                'name' => $announcedPeriod->name,
                'year' => $announcedPeriod->year,
            ] : null,
            'my_rankings' => is_array($myRankings) ? $myRankings : $myRankings->toArray(),
            'my_certificates' => is_array($myCertificates) ? $myCertificates : $myCertificates->toArray(),
        ];
    }

    /**
     * @return array<int, array{
     *     category: string,
     *     rank: int,
     *     score: float,
     *     votes_count: int,
     *     average_score: float
     * }>
     */
    private function buildEmployeeRankings(Period $period, Employee $employee): array
    {
        $categories = Category::query()
            ->orderBy('urutan')
            ->get(['id', 'nama']);

        $aggregates = Vote::query()
            ->select(
                'category_id',
                'employee_id',
                DB::raw('sum(total_score) as total_score'),
                DB::raw('count(*) as votes_count')
            )
            ->where('period_id', $period->id)
            ->groupBy('category_id', 'employee_id')
            ->get()
            ->groupBy('category_id');

        $rankings = [];

        foreach ($categories as $category) {
            $rows = $aggregates->get($category->id, collect())
                ->sortByDesc('total_score')
                ->values();

            if ($rows->isEmpty()) {
                continue;
            }

            $rank = 1;
            $index = 0;
            $previousScore = null;

            foreach ($rows as $row) {
                $index++;
                $score = (float) $row->total_score;

                if ($previousScore !== null && $score < $previousScore) {
                    $rank = $index;
                }

                $previousScore = $score;

                if ((int) $row->employee_id === $employee->id) {
                    $votesCount = (int) $row->votes_count;
                    $averageScore = $votesCount > 0 ? round($score / $votesCount, 2) : 0.0;

                    $rankings[] = [
                        'category' => $category->nama,
                        'rank' => $rank,
                        'score' => round($score, 2),
                        'votes_count' => $votesCount,
                        'average_score' => $averageScore,
                    ];
                    break;
                }
            }
        }

        return $rankings;
    }
}
