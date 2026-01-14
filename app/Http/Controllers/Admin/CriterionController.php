<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCriterionRequest;
use App\Http\Requests\UpdateCriterionRequest;
use App\Models\Category;
use App\Models\Criterion;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CriterionController extends Controller
{
    public function index(): Response
    {
        $criteria = Criterion::with('category')
            ->orderBy('category_id')
            ->orderBy('urutan')
            ->get();

        $categories = Category::orderBy('urutan')->get();

        return Inertia::render('Admin/Criteria/Index', [
            'criteria' => $criteria,
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        $categories = Category::orderBy('urutan')->get();

        return Inertia::render('Admin/Criteria/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreCriterionRequest $request): RedirectResponse
    {
        Criterion::create($request->validated());

        return redirect()
            ->route('admin.criteria.index')
            ->with('success', 'Kriteria berhasil dibuat');
    }

    public function show(Criterion $criterion): Response
    {
        $criterion->load('category', 'voteDetails');

        return Inertia::render('Admin/Criteria/Show', [
            'criterion' => $criterion,
        ]);
    }

    public function edit(Criterion $criterion): Response
    {
        $categories = Category::orderBy('urutan')->get();

        return Inertia::render('Admin/Criteria/Edit', [
            'criterion' => $criterion,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateCriterionRequest $request, Criterion $criterion): RedirectResponse
    {
        $criterion->update($request->validated());

        return redirect()
            ->route('admin.criteria.index')
            ->with('success', 'Kriteria berhasil diperbarui');
    }

    public function destroy(Criterion $criterion): RedirectResponse
    {
        $criterion->delete();

        return redirect()
            ->route('admin.criteria.index')
            ->with('success', 'Kriteria berhasil dihapus');
    }

    public function updateWeight(Criterion $criterion): RedirectResponse
    {
        $weight = request()->validate([
            'bobot' => 'required|numeric|min:0|max:100',
        ]);

        $criterion->update($weight);

        return back()->with('success', 'Bobot kriteria berhasil diperbarui');
    }
}
