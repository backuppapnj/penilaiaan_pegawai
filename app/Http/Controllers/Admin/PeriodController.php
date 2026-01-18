<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePeriodRequest;
use App\Http\Requests\UpdatePeriodRequest;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Period;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;

class PeriodController extends Controller
{
    public function index(): Response
    {
        $periods = Period::orderBy('year', 'desc')
            ->orderBy('semester', 'desc')
            ->get();

        return Inertia::render('Admin/Periods/Index', [
            'periods' => $periods,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Periods/Create');
    }

    public function store(StorePeriodRequest $request): RedirectResponse
    {
        Period::create($request->validated());

        return redirect()
            ->route('admin.periods.index')
            ->with('success', 'Periode berhasil dibuat');
    }

    public function show(Period $period): Response
    {
        $period->loadCount('votes');
        $pendingVotersByCategory = $this->getPendingVotersByCategory($period);
        $votes = Vote::query()
            ->with(['voter', 'employee.category'])
            ->where('period_id', $period->id)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Periods/Show', [
            'period' => $period,
            'votes' => $votes,
            'pendingVotersByCategory' => $pendingVotersByCategory,
        ]);
    }

    public function edit(Period $period): Response
    {
        return Inertia::render('Admin/Periods/Edit', [
            'period' => $period,
        ]);
    }

    public function update(UpdatePeriodRequest $request, Period $period): RedirectResponse
    {
        $period->update($request->validated());

        return redirect()
            ->route('admin.periods.index')
            ->with('success', 'Periode berhasil diperbarui');
    }

    public function destroy(Period $period): RedirectResponse
    {
        if ($period->status === 'open') {
            return back()->with('error', 'Tidak dapat menghapus periode yang sedang berlangsung');
        }

        $period->delete();

        return redirect()
            ->route('admin.periods.index')
            ->with('success', 'Periode berhasil dihapus');
    }

    public function updateStatus(Period $period, string $status): RedirectResponse
    {
        $validStatuses = ['draft', 'open', 'closed', 'announced'];

        if (! in_array($status, $validStatuses)) {
            return back()->with('error', 'Status tidak valid');
        }

        $period->update(['status' => $status]);

        $message = match ($status) {
            'open' => 'Periode dibuka untuk voting',
            'closed' => 'Periode ditutup',
            'announced' => 'Hasil periode diumumkan',
            'draft' => 'Periode dikembalikan ke draft',
            default => 'Status berhasil diperbarui',
        };

        return back()->with('success', $message);
    }

    /**
     * @return array<int, array{
     *     id: int,
     *     nama: string,
     *     pending_count: int,
     *     pending: array<int, array{
     *         id: int,
     *         nama: string,
     *         nip: string|null,
     *         completed: int,
     *         total: int,
     *         missing: int
     *     }>
     * }>
     */
    private function getPendingVotersByCategory(Period $period): array
    {
        $categories = Category::query()
            ->whereIn('id', [1, 2])
            ->orderBy('urutan')
            ->get(['id', 'nama']);

        if ($categories->isEmpty()) {
            return [];
        }

        $excludedNips = $this->getExcludedPimpinanNips();

        $eligibleEmployees = Employee::query()
            ->select(['id', 'category_id', 'nip'])
            ->whereIn('category_id', [1, 2])
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            })
            ->when(! empty($excludedNips), function ($query) use ($excludedNips) {
                $query->whereNotIn('nip', $excludedNips);
            })
            ->get();

        $eligibleCounts = $eligibleEmployees
            ->groupBy('category_id')
            ->map(fn ($group) => $group->count());

        $employeeCategoryById = $eligibleEmployees->pluck('category_id', 'id');

        $penilaiUsers = User::query()
            ->whereIn('role', ['Penilai', 'Peserta', 'Admin', 'SuperAdmin'])
            ->where('is_active', true)
            ->whereNotNull('employee_id')
            ->with('employee')
            ->orderBy('name')
            ->get();

        $votesByVoter = Vote::query()
            ->select('voter_id', 'category_id', DB::raw('count(*) as total'))
            ->where('period_id', $period->id)
            ->whereIn('category_id', [1, 2])
            ->groupBy('voter_id', 'category_id')
            ->get()
            ->groupBy('voter_id')
            ->map(fn ($rows) => $rows->pluck('total', 'category_id'));

        return $categories
            ->map(function ($category) use ($penilaiUsers, $eligibleCounts, $employeeCategoryById, $votesByVoter) {
                $pending = $penilaiUsers
                    ->map(function ($user) use ($category, $eligibleCounts, $employeeCategoryById, $votesByVoter) {
                        $total = (int) $eligibleCounts->get($category->id, 0);
                        $employeeId = $user->employee?->id;
                        $ownCategoryId = $employeeId ? $employeeCategoryById->get($employeeId) : null;

                        if ($ownCategoryId === $category->id) {
                            $total = max(0, $total - 1);
                        }

                        $completed = (int) ($votesByVoter->get($user->id)?->get($category->id) ?? 0);

                        if ($total === 0 || $completed >= $total) {
                            return null;
                        }

                        return [
                            'id' => $user->id,
                            'nama' => $user->employee?->nama ?? $user->name,
                            'nip' => $user->nip,
                            'completed' => $completed,
                            'total' => $total,
                            'missing' => $total - $completed,
                        ];
                    })
                    ->filter()
                    ->values();

                return [
                    'id' => $category->id,
                    'nama' => $category->nama,
                    'pending_count' => $pending->count(),
                    'pending' => $pending->all(),
                ];
            })
            ->values()
            ->all();
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
}
