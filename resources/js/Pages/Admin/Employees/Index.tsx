import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    ChevronLeft,
    ChevronRight,
    Download,
    Filter,
    Search,
    Users,
} from 'lucide-react';
import { FormEvent, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Pegawai', href: '/admin/employees' },
];

interface Category {
    id: number;
    nama: string;
    urutan: number;
}

interface Employee {
    id: number;
    nip: string;
    nama: string;
    jabatan: string;
    unit_kerja: string;
    golongan: string;
    category_id: number;
    category: Category;
}

interface PaginationLink {
    label: string;
    url: string | null;
    active: boolean;
}

interface PaginatedData {
    current_page: number;
    data: Employee[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

interface PageProps {
    employees: PaginatedData;
    categories: Category[];
    stats: {
        total: number;
        kategori1: number;
        kategori2: number;
        kategori3: number;
    };
    filters: {
        search?: string;
        category?: string;
    };
}

export default function EmployeesIndex({
    employees,
    categories,
    stats,
    filters,
}: PageProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [category, setCategory] = useState(filters.category || '');
    const [importing, setImporting] = useState(false);

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (category) params.append('category', category);

        router.get(`/admin/employees?${params.toString()}`);
    };

    const handleReset = () => {
        setSearch('');
        setCategory('');
        router.get('/admin/employees');
    };

    const handleImport = () => {
        if (
            !confirm(
                'Apakah Anda yakin ingin mengimpor data pegawai? Data yang ada akan ditimpa.',
            )
        ) {
            return;
        }

        setImporting(true);
        router.post(
            '/admin/employees/import',
            { truncate: true },
            {
                onFinish: () => setImporting(false),
            },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Kelola Pegawai" />

            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
                <div className="flex items-center justify-between">
                    <div className="space-y-2">
                        <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                            Data Pegawai
                        </h1>
                        <p className="text-muted-foreground">
                            Kelola data pegawai dan statistik kategori
                        </p>
                    </div>
                    <button
                        onClick={handleImport}
                        disabled={importing}
                        className="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-green-700 focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <Download className="size-4" />
                        {importing ? 'Mengimpor...' : 'Import dari JSON'}
                    </button>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-4">
                    <div className="rounded-xl border border-sidebar-border/70 bg-gradient-to-br from-blue-500 to-blue-600 p-6 text-white">
                        <div className="space-y-2">
                            <p className="text-sm font-medium opacity-90">
                                Total Pegawai
                            </p>
                            <p className="text-3xl font-bold">{stats.total}</p>
                        </div>
                    </div>
                    <div className="rounded-xl border border-sidebar-border/70 bg-gradient-to-br from-purple-500 to-purple-600 p-6 text-white">
                        <div className="space-y-2">
                            <p className="text-sm font-medium opacity-90">
                                Kategori 1
                            </p>
                            <p className="text-3xl font-bold">
                                {stats.kategori1}
                            </p>
                            <p className="text-xs opacity-80">
                                Pejabat Struktural/Fungsional
                            </p>
                        </div>
                    </div>
                    <div className="rounded-xl border border-sidebar-border/70 bg-gradient-to-br from-green-500 to-green-600 p-6 text-white">
                        <div className="space-y-2">
                            <p className="text-sm font-medium opacity-90">
                                Kategori 2
                            </p>
                            <p className="text-3xl font-bold">
                                {stats.kategori2}
                            </p>
                            <p className="text-xs opacity-80">Non-Pejabat</p>
                        </div>
                    </div>
                    <div className="rounded-xl border border-sidebar-border/70 bg-gradient-to-br from-orange-500 to-orange-600 p-6 text-white">
                        <div className="space-y-2">
                            <p className="text-sm font-medium opacity-90">
                                Kategori 3
                            </p>
                            <p className="text-3xl font-bold">
                                {stats.kategori3}
                            </p>
                            <p className="text-xs opacity-80">
                                Pemilih/Dinilai
                            </p>
                        </div>
                    </div>
                </div>

                {/* Filters */}
                <div className="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-800/50">
                    <form
                        onSubmit={handleSearch}
                        className="flex flex-wrap items-center gap-4"
                    >
                        <div className="min-w-[200px] flex-1">
                            <div className="relative">
                                <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Cari nama atau NIP..."
                                    className="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-10 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                />
                            </div>
                        </div>
                        <div className="min-w-[200px]">
                            <select
                                value={category}
                                onChange={(e) => setCategory(e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            >
                                <option value="">Semua Kategori</option>
                                {categories.map((cat) => (
                                    <option key={cat.id} value={cat.id}>
                                        {cat.nama}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="flex gap-2">
                            <button
                                type="submit"
                                className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                            >
                                <Filter className="size-4" />
                                Filter
                            </button>
                            <button
                                type="button"
                                onClick={handleReset}
                                className="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                            >
                                Reset
                            </button>
                        </div>
                    </form>
                </div>

                {/* Table */}
                <div className="rounded-xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-gray-800/50">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="border-b border-sidebar-border/70 bg-gray-50 dark:border-sidebar-border dark:bg-gray-800/30">
                                    <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase dark:text-gray-300">
                                        NIP
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase dark:text-gray-300">
                                        Nama
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase dark:text-gray-300">
                                        Jabatan
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase dark:text-gray-300">
                                        Unit Kerja
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase dark:text-gray-300">
                                        Golongan
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase dark:text-gray-300">
                                        Kategori
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                                {employees.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={6}
                                            className="px-6 py-12 text-center"
                                        >
                                            <Users className="mx-auto mb-3 size-12 text-gray-400" />
                                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                                Tidak ada data pegawai
                                            </p>
                                        </td>
                                    </tr>
                                ) : (
                                    employees.data.map((employee) => (
                                        <tr
                                            key={employee.id}
                                            className="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/30"
                                        >
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className="font-mono text-sm text-gray-700 dark:text-gray-300">
                                                    {employee.nip}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {employee.nama}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="text-sm text-gray-700 dark:text-gray-300">
                                                    {employee.jabatan}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="text-sm text-gray-700 dark:text-gray-300">
                                                    {employee.unit_kerja}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                    {employee.golongan}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                    {employee.category?.nama ||
                                                        '-'}
                                                </span>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {employees.last_page > 1 && (
                        <div className="flex items-center justify-between border-t border-sidebar-border/70 px-6 py-4 dark:border-sidebar-border">
                            <div className="text-sm text-gray-700 dark:text-gray-300">
                                Menampilkan {employees.from} - {employees.to}{' '}
                                dari {employees.total} pegawai
                            </div>
                            <div className="flex items-center gap-2">
                                <Link
                                    href={employees.prev_page_url || '#'}
                                    className={`rounded-lg p-2 transition-colors ${
                                        !employees.prev_page_url
                                            ? 'cursor-not-allowed opacity-50'
                                            : 'hover:bg-gray-100 dark:hover:bg-gray-800'
                                    }`}
                                    aria-label="Previous page"
                                >
                                    <ChevronLeft className="size-4" />
                                </Link>

                                <span className="text-sm text-gray-700 dark:text-gray-300">
                                    Halaman {employees.current_page} dari{' '}
                                    {employees.last_page}
                                </span>

                                <Link
                                    href={employees.next_page_url || '#'}
                                    className={`rounded-lg p-2 transition-colors ${
                                        !employees.next_page_url
                                            ? 'cursor-not-allowed opacity-50'
                                            : 'hover:bg-gray-100 dark:hover:bg-gray-800'
                                    }`}
                                    aria-label="Next page"
                                >
                                    <ChevronRight className="size-4" />
                                </Link>
                            </div>
                        </div>
                    )}
                </div>

                <div className="flex items-center justify-between">
                    <Link
                        href="/admin"
                        className="inline-flex items-center gap-2 text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        <ArrowLeft className="size-4" />
                        Kembali ke Dashboard
                    </Link>
                </div>
            </div>
        </AppLayout>
    );
}
