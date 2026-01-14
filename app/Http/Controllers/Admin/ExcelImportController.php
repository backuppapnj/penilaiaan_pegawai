<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportSikepRequest;
use App\Models\DisciplineScore;
use App\Models\Period;
use App\Services\SikepImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ExcelImportController extends Controller
{
    public function __construct(
        private SikepImportService $sikepService
    ) {
    }

    /**
     * Display the SIKEP import page.
     */
    public function index(): Response
    {
        // $periods = Period::orderBy('year', 'desc')
        //     ->orderBy('semester')
        //     ->get(['id', 'name', 'semester', 'year', 'status']);

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $currentYear = date('Y');
        $years = range($currentYear - 1, $currentYear + 1);

        $recentImports = DisciplineScore::with(['employee:id,nama,nip,jabatan'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($score) use ($months) {
                return [
                    'id' => $score->id,
                    'employee' => [
                        'nama' => $score->employee->nama,
                        'nip' => $score->employee->nip,
                        'jabatan' => $score->employee->jabatan,
                    ],
                    'period' => [
                        'name' => ($months[$score->month] ?? $score->month) . ' ' . $score->year,
                        'year' => $score->year,
                    ],
                    'final_score' => $score->final_score,
                    'rank' => $score->rank,
                    'created_at' => $score->created_at->format('d M Y H:i'),
                ];
            });

        return Inertia::render('Admin/SikepImport/Index', [
            'months' => $months,
            'years' => $years,
            'recentImports' => $recentImports,
        ]);
    }

    /**
     * Handle the SIKEP Excel file import.
     */
    public function store(ImportSikepRequest $request): JsonResponse
    {
        try {
            $file = $request->file('excel_file');
            $month = (int) $request->input('month');
            $year = (int) $request->input('year');

            // Store the file for backup/audit
            $fileName = "sikep_{$year}_{$month}_".time().'.'.$file->getClientOriginalExtension();
            $file->storeAs('sikep-imports', $fileName, 'local');

            // Process the import
            $result = $this->sikepService->import($file, $month, $year);

            return response()->json([
                'success' => true,
                'message' => "Import berhasil! {$result['success']} pegawai diproses.",
                'data' => [
                    'success' => $result['success'],
                    'failed' => $result['failed'],
                    'errors' => $result['errors'],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import gagal: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get discipline scores for a specific period.
     */
    public function scores(Request $request): JsonResponse
    {
        $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer'],
        ]);

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $month = (int) $request->input('month');
        $year = (int) $request->input('year');

        $scores = DisciplineScore::with(['employee:id,nama,nip,jabatan'])
            ->where('month', $month)
            ->where('year', $year)
            ->orderBy('rank')
            ->get()
            ->map(function ($score) use ($months) {
                return [
                    'id' => $score->id,
                    'rank' => $score->rank,
                    'employee' => [
                        'nama' => $score->employee->nama,
                        'nip' => $score->employee->nip,
                        'jabatan' => $score->employee->jabatan,
                    ],
                    'period' => [
                        'name' => ($months[$score->month] ?? $score->month) . ' ' . $score->year,
                    ],
                    'scores' => [
                        'score_1' => $score->score_1,
                        'score_2' => $score->score_2,
                        'score_3' => $score->score_3,
                        'final_score' => $score->final_score,
                    ],
                    'attendance' => [
                        'total_work_days' => $score->total_work_days,
                        'present_on_time' => $score->present_on_time,
                        'leave_on_time' => $score->leave_on_time,
                        'late_minutes' => $score->late_minutes,
                        'early_leave_minutes' => $score->early_leave_minutes,
                        'excess_permission_count' => $score->excess_permission_count,
                    ],
                    'created_at' => $score->created_at->format('d M Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $scores,
        ]);
    }

    /**
     * Delete a discipline score record.
     */
    public function destroy(int $id): JsonResponse
    {
        $score = DisciplineScore::findOrFail($id);
        $score->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }
}
