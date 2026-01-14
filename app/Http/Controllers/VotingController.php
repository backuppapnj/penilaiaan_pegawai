<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoteRequest;
use App\Models\Category;
use App\Models\Criterion;
use App\Models\Employee;
use App\Models\Period;
use App\Models\Vote;
use App\Models\VoteDetail;
use Illuminate\Http\RedirectResponse;
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

        $employees = Employee::with('category')
            ->where('id', '!=', $employeeId)
            ->whereNotNull('category_id')
            ->whereNotIn('id', $votedEmployees)
            ->whereNotIn('jabatan', [
                'Ketua Pengadilan Tingkat Pertama Klas II',
                'Wakil Ketua Tingkat Pertama',
                'Hakim Tingkat Pertama',
                'Panitera Tingkat Pertama Klas II',
                'Sekretaris Tingkat Pertama Klas II',
            ])
            ->get();

        $eligibleEmployeeCounts = Employee::select('category_id', DB::raw('count(*) as total'))
            ->where('id', '!=', $employeeId)
            ->whereNotNull('category_id')
            ->whereNotIn('jabatan', [
                'Ketua Pengadilan Tingkat Pertama Klas II',
                'Wakil Ketua Tingkat Pertama',
                'Hakim Tingkat Pertama',
                'Panitera Tingkat Pertama Klas II',
                'Sekretaris Tingkat Pertama Klas II',
            ])
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        return Inertia::render('Penilai/Voting/Index', [
            'activePeriod' => $activePeriod,
            'categories' => $categories,
            'employees' => $employees,
            'votedEmployees' => $votedEmployees,
            'eligibleEmployeeCounts' => $eligibleEmployeeCounts,
        ]);
    }

    public function show(Period $period, Category $category): Response
    {
        $period->load('votes');

        $criteria = $category->criteria()->orderBy('urutan')->get();

        $userId = auth()->id();
        $employeeId = auth()->user()?->employee?->id;

        $votedEmployeeIds = Vote::where('period_id', $period->id)
            ->where('voter_id', $userId)
            ->where('category_id', $category->id)
            ->pluck('employee_id');

        $employees = Employee::with('category')
            ->where('category_id', $category->id)
            ->where('id', '!=', $employeeId)
            ->whereNotIn('id', $votedEmployeeIds)
            ->whereNotIn('jabatan', [
                'Ketua Pengadilan Tingkat Pertama Klas II',
                'Wakil Ketua Tingkat Pertama',
                'Hakim Tingkat Pertama',
                'Panitera Tingkat Pertama Klas II',
                'Sekretaris Tingkat Pertama Klas II',
            ])
            ->get();

        return Inertia::render('Penilai/Voting/Show', [
            'period' => $period,
            'category' => $category,
            'criteria' => $criteria,
            'employees' => $employees,
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
}
