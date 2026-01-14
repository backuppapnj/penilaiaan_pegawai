<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePeriodRequest;
use App\Http\Requests\UpdatePeriodRequest;
use App\Models\Period;
use Illuminate\Http\RedirectResponse;
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
        $period->load(['votes.voter', 'votes.employee.category']);

        return Inertia::render('Admin/Periods/Show', [
            'period' => $period,
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
}
