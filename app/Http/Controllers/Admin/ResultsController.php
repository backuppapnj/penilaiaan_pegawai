<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Period;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ResultsController extends Controller
{
    public function index(Request $request): Response
    {
        $periods = Period::query()
            ->orderBy('year', 'desc')
            ->orderBy('semester', 'desc')
            ->get(['id', 'name', 'status', 'year', 'semester']);

        $periodId = $request->integer('period_id');
        $selectedPeriod = $periodId
            ? $periods->firstWhere('id', $periodId)
            : null;

        if (! $selectedPeriod) {
            $selectedPeriod = Period::query()
                ->where('status', 'announced')
                ->latest()
                ->first()
                ?? Period::query()->latest()->first();
        }

        $categories = Category::query()
            ->orderBy('urutan')
            ->get(['id', 'nama', 'deskripsi', 'urutan']);

        $results = [];

        if ($selectedPeriod) {
            foreach ($categories as $category) {
                $rows = $this->buildCategoryResults($selectedPeriod->id, $category->id);

                $results[] = [
                    'category' => [
                        'id' => $category->id,
                        'nama' => $category->nama,
                        'deskripsi' => $category->deskripsi,
                    ],
                    'rows' => $rows,
                ];
            }
        }

        return Inertia::render('Admin/Results/Index', [
            'periods' => $periods,
            'selectedPeriod' => $selectedPeriod
                ? [
                    'id' => $selectedPeriod->id,
                    'name' => $selectedPeriod->name,
                    'status' => $selectedPeriod->status,
                    'year' => $selectedPeriod->year,
                    'semester' => $selectedPeriod->semester,
                ]
                : null,
            'results' => $results,
        ]);
    }

    /**
     * @return array<int, array{
     *     rank: int,
     *     votes_count: int,
     *     total_score: float,
     *     average_score: float,
     *     is_winner: bool,
     *     employee: array{
     *         id: int|null,
     *         nama: string,
     *         nip: string|null,
     *         jabatan: string|null,
     *         unit_kerja: string|null
     *     }
     * }>
     */
    private function buildCategoryResults(int $periodId, int $categoryId): array
    {
        $aggregates = Vote::query()
            ->select(
                'employee_id',
                DB::raw('count(*) as votes_count'),
                DB::raw('sum(total_score) as total_score')
            )
            ->where('period_id', $periodId)
            ->where('category_id', $categoryId) // Ini sudah memfilter berdasarkan kategori yang diminta (1 atau 2), jadi kategori 3 otomatis tidak ikut.
            ->groupBy('employee_id')
            ->orderByDesc('total_score')
            ->get();
// ...
        if ($aggregates->isEmpty()) {
            return [];
        }

        $employees = Employee::query()
            ->whereIn('id', $aggregates->pluck('employee_id'))
            ->get(['id', 'nama', 'nip', 'jabatan', 'unit_kerja'])
            ->keyBy('id');

        $rows = [];
        $rank = 1;
        $previousScore = null;
        $index = 0;

        foreach ($aggregates as $aggregate) {
            $index++;
            $totalScore = (float) $aggregate->total_score;

            if ($previousScore !== null && $totalScore < $previousScore) {
                $rank = $index;
            }

            $employee = $employees->get($aggregate->employee_id);
            $votesCount = (int) $aggregate->votes_count;
            $averageScore = $votesCount > 0 ? round($totalScore / $votesCount, 2) : 0.0;

            $rows[] = [
                'rank' => $rank,
                'votes_count' => $votesCount,
                'total_score' => round($totalScore, 2),
                'average_score' => $averageScore,
                'is_winner' => $rank === 1,
                'employee' => [
                    'id' => $employee?->id,
                    'nama' => $employee?->nama ?? 'Tidak diketahui',
                    'nip' => $employee?->nip,
                    'jabatan' => $employee?->jabatan,
                    'unit_kerja' => $employee?->unit_kerja,
                ],
            ];

            $previousScore = $totalScore;
        }

        return $rows;
    }
}
