<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoteRequest;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Period;
use App\Models\Vote;
use App\Models\VoteDetail;
use App\Services\DisciplineVoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class VotingController extends Controller
{
    public function index(): Response
    {
        $activePeriod = Period::where('status', 'open')->first();

        if (! $activePeriod) {
            return Inertia::render('Penilai/Voting/Index', [
                'activePeriod' => null,
                'categories' => [],
                'employees' => [],
                'criteria' => [],
                'votedEmployees' => [],
            ]);
        }

        $categories = Category::with('criteria')->orderBy('urutan')->get();

        $userId = auth()->id();
        $employeeId = auth()->user()?->employee?->id;

        $votedEmployees = Vote::where('period_id', $activePeriod->id)
            ->where('voter_id', $userId)
            ->pluck('employee_id');

        // Filter untuk halaman index: hanya Pimpinan yang dikecualikan
        $employees = Employee::with('category')
            ->where('id', '!=', $employeeId)
            ->whereNotNull('category_id')
            ->whereNotIn('id', $votedEmployees)
            ->where('jabatan', 'not like', '%Ketua%')
            ->where('jabatan', 'not like', '%Wakil%')
            ->where('jabatan', 'not like', '%Panitera%')
            ->where('jabatan', 'not like', '%Sekretaris%')
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
            ->where('jabatan', 'not like', '%Ketua%')
            ->where('jabatan', 'not like', '%Wakil%')
            ->where('jabatan', 'not like', '%Panitera%')
            ->where('jabatan', 'not like', '%Sekretaris%')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        foreach ($categoryCounts as $catId => $count) {
            $eligibleEmployeeCounts[$catId] = $count;
        }

        // Kategori 3 (Pegawai Disiplin): hitung semua pegawai kecuali pimpinan
        $disiplinCount = Employee::where('id', '!=', $employeeId)
            ->whereNotNull('category_id')
            ->where('jabatan', 'not like', '%Ketua%')
            ->where('jabatan', 'not like', '%Wakil%')
            ->where('jabatan', 'not like', '%Panitera%')
            ->where('jabatan', 'not like', '%Sekretaris%')
            ->count();

        $eligibleEmployeeCounts[3] = $disiplinCount;

        // Hitung remaining counts (pegawai yang belum di-vote per kategori)
        $remainingCounts = [];

        // Kategori 1 & 2: hitung berdasarkan category_id
        foreach ([1, 2] as $catId) {
            $count = Employee::where('id', '!=', $employeeId)
                ->where('category_id', $catId)
                ->whereNotIn('id', $votedEmployees)
                ->where('jabatan', 'not like', '%Ketua%')
                ->where('jabatan', 'not like', '%Wakil%')
                ->where('jabatan', 'not like', '%Panitera%')
                ->where('jabatan', 'not like', '%Sekretaris%')
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
            ->where('jabatan', 'not like', '%Ketua%')
            ->where('jabatan', 'not like', '%Wakil%')
            ->where('jabatan', 'not like', '%Panitera%')
            ->where('jabatan', 'not like', '%Sekretaris%')
            ->count();

        return Inertia::render('Penilai/Voting/Index', [
            'activePeriod' => $activePeriod,
            'categories' => $categories,
            'employees' => $employees,
            'votedEmployees' => $votedEmployees,
            'eligibleEmployeeCounts' => $eligibleEmployeeCounts,
            'remainingCounts' => $remainingCounts,
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
        $isResultsLocked = $isAutomaticVoting && ! $isAdmin && $period->status !== 'announced';

        // For automatic voting, get all generated votes
        $automaticVotes = null;
        if ($isAutomaticVoting && ! $isResultsLocked) {
            $automaticVotes = Vote::where('period_id', $period->id)
                ->where('category_id', $category->id)
                ->whereHas('employee', function ($query) {
                    $query->where('jabatan', 'not like', '%Ketua%')
                        ->where('jabatan', 'not like', '%Wakil%')
                        ->where('jabatan', 'not like', '%Panitera%')
                        ->where('jabatan', 'not like', '%Sekretaris%');
                })
                ->with(['employee', 'voteDetails.criterion', 'voter'])
                ->get()
                ->sortByDesc('total_score')
                ->values();
        }

        $votedEmployeeIds = Vote::where('period_id', $period->id)
            ->where('voter_id', $userId)
            ->where('category_id', $category->id)
            ->pluck('employee_id');

        $employeesQuery = Employee::with('category')
            ->where('id', '!=', $employeeId)
            ->whereNotIn('id', $votedEmployeeIds);

        if ($isAutomaticVoting) {
            $employeesQuery->where('jabatan', 'not like', '%Ketua%')
                ->where('jabatan', 'not like', '%Wakil%')
                ->where('jabatan', 'not like', '%Panitera%')
                ->where('jabatan', 'not like', '%Sekretaris%');
        } else {
            $employeesQuery->where('category_id', $category->id)
                ->whereNotIn('jabatan', [
                    'Ketua Pengadilan Tingkat Pertama Klas II',
                    'Wakil Ketua Tingkat Pertama',
                    'Hakim Tingkat Pertama',
                    'Panitera Tingkat Pertama Klas II',
                    'Sekretaris Tingkat Pertama Klas II',
                ]);
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
            ->get();

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
        $result = $service->generateVotes($period->id, auth()->id(), ['overwrite' => $overwrite]);

        if ($result['success'] === 0 && ! empty($result['errors'])) {
            return back()->with('error', 'Gagal generate voting otomatis. '.$result['errors'][0]);
        }

        if ($result['failed'] > 0) {
            return back()->with('error', "Sebagian data gagal diproses ({$result['failed']} pegawai).");
        }

        return back()->with('success', "Voting otomatis berhasil dibuat untuk {$result['success']} pegawai.");
    }
}
