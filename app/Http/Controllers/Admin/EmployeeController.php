<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(Request $request)
    {
        $query = Employee::query()->with('category');

        // Filter by category
        if ($request->has('category') && $request->category !== '') {
            $query->where('category_id', $request->category);
        }

        // Search by name or NIP
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('category_id')
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::all();

        // Category statistics
        $kategori1 = Category::where('nama', 'Pejabat Struktural/Fungsional')->first();
        $kategori2 = Category::where('nama', 'Non-Pejabat')->first();

        $stats = [
            'total' => Employee::count(),
            'kategori1' => $kategori1 ? Employee::where('category_id', $kategori1->id)->count() : 0,
            'kategori2' => $kategori2 ? Employee::where('category_id', $kategori2->id)->count() : 0,
            'kategori3' => 23, // Kategori 3 is dynamic: 29 total - 6 excluded (4 pimpinan + 2 hakim)
        ];

        return Inertia::render('Admin/Employees/Index', [
            'employees' => $employees,
            'categories' => $categories,
            'stats' => $stats,
            'filters' => [
                'search' => $request->search,
                'category' => $request->category,
            ],
        ]);
    }

    /**
     * Import employees from JSON files.
     */
    public function import(Request $request)
    {
        $request->validate([
            'truncate' => 'sometimes|boolean',
        ]);

        try {
            $exitCode = Artisan::call('employees:import', [
                '--json-path' => 'docs/data_pegawai.json',
                '--org-path' => 'docs/org_structure.json',
                '--truncate' => $request->boolean('truncate', false),
            ]);

            $output = Artisan::output();

            if ($exitCode === 0) {
                return back()->with('success', 'Employees imported successfully.');
            }

            return back()->with('error', "Import failed: {$output}");
        } catch (\Exception $e) {
            return back()->with('error', "Import failed: {$e->getMessage()}");
        }
    }

    /**
     * Get employee statistics.
     */
    public function stats()
    {
        $kategori1 = Category::where('nama', 'Pejabat Struktural/Fungsional')->first();
        $kategori2 = Category::where('nama', 'Non-Pejabat')->first();

        return response()->json([
            'total' => Employee::count(),
            'category_1' => $kategori1 ? Employee::where('category_id', $kategori1->id)->count() : 0,
            'category_2' => $kategori2 ? Employee::where('category_id', $kategori2->id)->count() : 0,
            'category_3' => 23, // Kategori 3 is dynamic: 29 total - 6 excluded
        ]);
    }
}
