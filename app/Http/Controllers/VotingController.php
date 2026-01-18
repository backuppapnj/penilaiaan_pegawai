<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoteRequest;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Period;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteDetail;
use App\Services\DisciplineVoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;

class VotingController extends Controller
{
    public function index(): Response
    {
        $activePeriod = Period::where('status', 'open')->first();
        $user = auth()->user();
        $canViewDisciplineResults = $this->canViewDisciplineResults($user);

        if (! $activePeriod) {
            return Inertia::render('Penilai/Voting/Index', [
                'activePeriod' => null,
                'categories' => [],
                'employees' => [],
                'criteria' => [],
                'votedEmployees' => [],
                'eligibleEmployeeCounts' => [],
                'remainingCounts' => [],
                'disciplineVotesCount' => 0,
                'canViewDisciplineResults' => $canViewDisciplineResults,
            ]);
        }

        $categories = Category::with('criteria')->orderBy('urutan')->get();
        $disciplineVotesCount = Vote::where('period_id', $activePeriod->id)
            ->where('category_id', 3)
            ->count();

        $userId = auth()->id();
        $employeeId = auth()->user()?->employee?->id;

        $votedByCategory = Vote::where('period_id', $activePeriod->id)
            ->where('voter_id', $userId)
            ->get()
            ->groupBy('category_id')
            ->map(fn ($votes) => $votes->pluck('employee_id'));

        $votedEmployees = $votedByCategory
            ->flatten()
            ->unique()
            ->values();

        $excludedNips = $this->getExcludedPimpinanNips();

        // Filter untuk halaman index: hanya Pimpinan yang dikecualikan
        $employees = Employee::with('category')
            ->where('id', '!=', $employeeId)
            ->whereNotNull('category_id')
            ->whereNotIn('id', $votedEmployees)
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            })
            ->when(! empty($excludedNips), function ($query) use ($excludedNips) {
                $query->whereNotIn('nip', $excludedNips);
            })
            ->get();

        // Hitung eligible employees per kategori
        // Untuk kategori 1 & 2: gunakan category_id
        // Untuk kategori 3 (Pegawai Disiplin): hitung semua pegawai kecuali pimpinan
        $eligibleEmployeeCounts = [];

        // Kategori 1 & 2: gunakan category_id
        $categoryCounts = Employee::select('category_id', DB::raw('count(*) as total'))
            ->where('id', '!=', $employeeId)
            ->whereNotNull('category_id')
            ->whereIn('category_id', [1, 2])
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            })
            ->when(! empty($excludedNips), function ($query) use ($excludedNips) {
                $query->whereNotIn('nip', $excludedNips);
            })
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        foreach ($categoryCounts as $catId => $count) {
            $eligibleEmployeeCounts[$catId] = $count;
        }

        // Kategori 3 (Pegawai Disiplin): hitung semua pegawai kecuali pimpinan
        $disiplinCount = Employee::where('id', '!=', $employeeId)
            ->whereNotNull('category_id')
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            })
            ->when(! empty($excludedNips), function ($query) use ($excludedNips) {
                $query->whereNotIn('nip', $excludedNips);
            })
            ->count();

        $eligibleEmployeeCounts[3] = $disiplinCount;

        // Hitung remaining counts (pegawai yang belum di-vote per kategori)
        $remainingCounts = [];

        // Kategori 1 & 2: hitung berdasarkan category_id
        foreach ([1, 2] as $catId) {
            $votedForCategory = $votedByCategory->get($catId, collect());
            $count = Employee::where('id', '!=', $employeeId)
                ->where('category_id', $catId)
                ->whereNotIn('id', $votedForCategory)
                ->whereHas('user', function ($query) {
                    $query->where('is_active', true);
                })
                ->when(! empty($excludedNips), function ($query) use ($excludedNips) {
                    $query->whereNotIn('nip', $excludedNips);
                })
                ->count();
            $remainingCounts[$catId] = $count;
        }

        // Kategori 3 (Pegawai Disiplin): semua pegawai kecuali pimpinan dan sudah di-vote
        // Note: votedEmployees untuk kategori 3 dihitung berbeda, perlu query khusus
        $votedForCategory3 = Vote::where('period_id', $activePeriod->id)
            ->where('voter_id', $userId)
            ->where('category_id', 3)
            ->pluck('employee_id');

        $remainingCounts[3] = Employee::where('id', '!=', $employeeId)
            ->whereNotNull('category_id')
            ->whereNotIn('id', $votedForCategory3)
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            })
            ->when(! empty($excludedNips), function ($query) use ($excludedNips) {
                $query->whereNotIn('nip', $excludedNips);
            })
            ->count();

        return Inertia::render('Penilai/Voting/Index', [
            'activePeriod' => $activePeriod,
            'categories' => $categories,
            'employees' => $employees,
            'votedEmployees' => $votedEmployees,
            'eligibleEmployeeCounts' => $eligibleEmployeeCounts,
            'remainingCounts' => $remainingCounts,
            'disciplineVotesCount' => $disciplineVotesCount,
            'canViewDisciplineResults' => $canViewDisciplineResults,
        ]);
    }

    public function show(Period $period, Category $category): Response
    {
        $period->load('votes');

        $criteria = $category->criteria()->orderBy('urutan')->get();

        $userId = auth()->id();
        $employeeId = auth()->user()?->employee?->id;

        $disciplineCategoryId = 3;
        $isAutomaticVoting = $category->id === $disciplineCategoryId;
        $user = auth()->user();
        $isAdmin = $user?->hasRole('Admin', 'SuperAdmin') ?? false;
        $canViewDisciplineResults = $this->canViewDisciplineResults($user);
        $isResultsLocked = $isAutomaticVoting && ! $canViewDisciplineResults && $period->status !== 'announced';
        $excludedNips = $this->getExcludedPimpinanNips();

        // For automatic voting, get all generated votes
        $automaticVotes = null;
        if ($isAutomaticVoting && ! $isResultsLocked) {
            $automaticVotes = Vote::where('period_id', $period->id)
                ->where('category_id', $category->id)
                ->whereHas('employee', function ($query) use ($excludedNips) {
                    $query->whereHas('user', function ($query) {
                        $query->where('is_active', true);
                    });
                    if (! empty($excludedNips)) {
                        $query->whereNotIn('nip', $excludedNips);
                    }
                })
                ->with(['employee', 'voteDetails.criterion', 'voter'])
                ->orderByDesc('total_score')
                ->orderByDesc('early_arrival_count')
                ->get();
        }

        $votedEmployeeIds = Vote::where('period_id', $period->id)
            ->where('voter_id', $userId)
            ->where('category_id', $category->id)
            ->pluck('employee_id');

        $employeesQuery = Employee::with('category')
            ->where('id', '!=', $employeeId)
            ->whereNotIn('id', $votedEmployeeIds)
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            });

        if ($isAutomaticVoting) {
            if (! empty($excludedNips)) {
                $employeesQuery->whereNotIn('nip', $excludedNips);
            }
        } else {
            $employeesQuery->where('category_id', $category->id)
                ->when(! empty($excludedNips), function ($query) use ($excludedNips) {
                    $query->whereNotIn('nip', $excludedNips);
                });
        }

        $employees = $employeesQuery->get();

        return Inertia::render('Penilai/Voting/Show', [
            'period' => $period,
            'category' => $category,
            'criteria' => $criteria,
            'employees' => $employees,
            'isAutomaticVoting' => $isAutomaticVoting,
            'automaticVotes' => $automaticVotes,
            'isResultsLocked' => $isResultsLocked,
        ]);
    }

    public function store(StoreVoteRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $period = Period::findOrFail($validated['period_id']);

        if (! $period->isOpen()) {
            return back()->with('error', 'Periode tidak sedang dibuka untuk voting');
        }

        $voterId = auth()->id();

        DB::transaction(function () use ($validated, $voterId) {
            $scores = $validated['scores'];

            $totalScore = collect($scores)->sum('score');

            $vote = Vote::create([
                'period_id' => $validated['period_id'],
                'voter_id' => $voterId,
                'employee_id' => $validated['employee_id'],
                'category_id' => $validated['category_id'],
                'scores' => $scores,
                'total_score' => $totalScore,
            ]);

            foreach ($scores as $scoreData) {
                VoteDetail::create([
                    'vote_id' => $vote->id,
                    'criterion_id' => $scoreData['criterion_id'],
                    'score' => $scoreData['score'],
                ]);
            }
        });

        return back()->with('success', 'Terima kasih! Nilai Anda berhasil disimpan');
    }

    public function history(): Response
    {
        $voterId = auth()->id();

        $votes = Vote::with(['period', 'employee', 'category', 'voteDetails.criterion'])
            ->where('voter_id', $voterId)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Penilai/Voting/History', [
            'votes' => $votes,
        ]);
    }

    public function generateAutomaticDisciplineVotes(
        Request $request,
        Period $period,
        Category $category,
        DisciplineVoteService $service
    ): RedirectResponse {
        $disciplineCategoryId = 3;

        if ($category->id !== $disciplineCategoryId) {
            return back()->with('error', 'Kategori ini tidak mendukung voting otomatis.');
        }

        $overwrite = $request->boolean('overwrite');

        if (! $overwrite && $service->hasDisciplineVotes($period->id)) {
            return back()->with('success', 'Voting otomatis sudah di-generate.');
        }

        $result = $service->generateVotes($period->id, auth()->id(), ['overwrite' => $overwrite]);

        if ($result['success'] === 0 && ! empty($result['errors'])) {
            return back()->with('error', 'Gagal generate voting otomatis. '.$result['errors'][0]);
        }

        if ($result['failed'] > 0) {
            return back()->with('error', "Sebagian data gagal diproses ({$result['failed']} pegawai).");
        }

        return back()->with('success', "Voting otomatis berhasil dibuat untuk {$result['success']} pegawai.");
    }

    /**
     * @return array<int, string>
     */
    private function getExcludedPimpinanNips(): array
    {
        $path = base_path('docs/org_structure.json');
        if (! File::exists($path)) {
            return [];
        }

        $org = json_decode(File::get($path), true);
        if (! is_array($org)) {
            return [];
        }

        $nips = [];

        foreach ($org['pimpinan'] ?? [] as $pimpinan) {
            if (! empty($pimpinan['nip'])) {
                $nips[] = $pimpinan['nip'];
            }
        }

        if (! empty($org['panitera']['panitera']['nip'])) {
            $nips[] = $org['panitera']['panitera']['nip'];
        }

        if (! empty($org['sekretariat']['sekretaris']['nip'])) {
            $nips[] = $org['sekretariat']['sekretaris']['nip'];
        }

        return array_values(array_unique($nips));
    }

    /**
     * @return array<int, string>
     */
    private function getDisciplineResultViewerNips(): array
    {
        $path = base_path('docs/org_structure.json');
        if (! File::exists($path)) {
            return [];
        }

        $org = json_decode(File::get($path), true);
        if (! is_array($org)) {
            return [];
        }

        $nips = [];

        foreach ($org['pimpinan'] ?? [] as $pimpinan) {
            if (! empty($pimpinan['nip'])) {
                $nips[] = $pimpinan['nip'];
            }
        }

        if (! empty($org['panitera']['panitera']['nip'])) {
            $nips[] = $org['panitera']['panitera']['nip'];
        }

        return array_values(array_unique($nips));
    }

    private function canViewDisciplineResults(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('Admin', 'SuperAdmin')) {
            return true;
        }

        $nip = $user->nip ?? $user->employee?->nip;
        if (! $nip) {
            return false;
        }

        return in_array($nip, $this->getDisciplineResultViewerNips(), true);
    }
}
