import StatusBadge from '@/components/dashboard/status-badge';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Trophy } from 'lucide-react';
import { type ChangeEvent } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Hasil Penilaian', href: '/admin/results' },
];

interface Period {
    id: number;
    name: string;
    status: 'draft' | 'open' | 'closed' | 'announced';
    year: number;
    semester: string;
}

interface CategoryResultRow {
    rank: number;
    votes_count: number;
    total_score: number;
    average_score: number;
    is_winner: boolean;
    employee: {
        id: number | null;
        nama: string;
        nip: string | null;
        jabatan: string | null;
        unit_kerja: string | null;
    };
}

interface CategoryResult {
    category: {
        id: number;
        nama: string;
        deskripsi: string | null;
    };
    rows: CategoryResultRow[];
}

interface PageProps {
    periods: Period[];
    selectedPeriod: Period | null;
    results: CategoryResult[];
}

export default function ResultsIndex({ periods, selectedPeriod, results }: PageProps) {
    const handlePeriodChange = (event: ChangeEvent<HTMLSelectElement>) => {
        const value = event.target.value;
        router.get(
            '/admin/results',
            value ? { period_id: value } : {},
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Hasil Penilaian" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div className="space-y-2">
                        <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                            Hasil Penilaian
                        </h1>
                        <p className="text-muted-foreground">
                            Ringkasan peringkat dan skor seluruh pegawai per kategori.
                        </p>
                    </div>
                    <div className="flex flex-col items-start gap-2">
                        <label
                            htmlFor="period"
                            className="text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Pilih Periode
                        </label>
                        <select
                            id="period"
                            value={selectedPeriod?.id ?? ''}
                            onChange={handlePeriodChange}
                            className="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        >
                            <option value="">Pilih periode</option>
                            {periods.map((period) => (
                                <option key={period.id} value={period.id}>
                                    {period.name} ({period.year})
                                </option>
                            ))}
                        </select>
                    </div>
                </div>

                {!selectedPeriod ? (
                    <div className="rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-gray-800 dark:bg-gray-900">
                        <Trophy className="mx-auto mb-4 size-12 text-gray-400" />
                        <h2 className="mb-2 text-xl font-semibold text-gray-900 dark:text-gray-100">
                            Belum Ada Periode
                        </h2>
                        <p className="text-gray-600 dark:text-gray-400">
                            Buat periode terlebih dahulu untuk melihat hasil penilaian.
                        </p>
                    </div>
                ) : (
                    <>
                        <div className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:bg-gray-900">
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                        Periode Terpilih
                                    </p>
                                    <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                        {selectedPeriod.name} ({selectedPeriod.year})
                                    </h2>
                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                        Semester {selectedPeriod.semester}
                                    </p>
                                </div>
                                <StatusBadge status={selectedPeriod.status} />
                            </div>
                        </div>

                        <div className="flex flex-col gap-6">
                            {results.map((result) => (
                                <div
                                    key={result.category.id}
                                    className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:bg-gray-900"
                                >
                                    <div className="mb-4">
                                        <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            {result.category.nama}
                                        </h3>
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            {result.category.deskripsi || 'Tidak ada deskripsi'}
                                        </p>
                                    </div>

                                    {result.rows.length === 0 ? (
                                        <div className="rounded-lg border border-gray-200 bg-gray-50 p-6 text-center text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-400">
                                            Belum ada data penilaian untuk kategori ini.
                                        </div>
                                    ) : (
                                        <div className="overflow-x-auto">
                                            <table className="w-full">
                                                <thead>
                                                    <tr className="border-b border-gray-200 dark:border-gray-800">
                                                        <th className="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                            Peringkat
                                                        </th>
                                                        <th className="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                            Pegawai
                                                        </th>
                                                        <th className="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                            NIP
                                                        </th>
                                                        <th className="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                            Jabatan
                                                        </th>
                                                        <th className="px-4 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                            Total Skor
                                                        </th>
                                                        <th className="px-4 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                            Rata-rata
                                                        </th>
                                                        <th className="px-4 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                            Jumlah Vote
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                                                    {result.rows.map((row) => {
                                                        const isWinner = row.is_winner;

                                                        return (
                                                        <tr
                                                            key={`${result.category.id}-${row.employee.id}-${row.rank}`}
                                                            className={
                                                                isWinner
                                                                    ? 'bg-yellow-50 dark:bg-yellow-900/20'
                                                                    : 'hover:bg-gray-50 dark:hover:bg-gray-800/50'
                                                            }
                                                        >
                                                            <td
                                                                className={`px-4 py-3 text-sm font-semibold ${
                                                                    isWinner
                                                                        ? 'text-gray-900 dark:text-yellow-400'
                                                                        : 'text-gray-900 dark:text-gray-100'
                                                                }`}
                                                            >
                                                                #{row.rank}
                                                            </td>
                                                            <td
                                                                className={`px-4 py-3 text-sm ${
                                                                    isWinner
                                                                        ? 'text-gray-900 dark:text-gray-100'
                                                                        : 'text-gray-900 dark:text-gray-100'
                                                                }`}
                                                            >
                                                                <span className={isWinner ? 'font-medium' : ''}>
                                                                    {row.employee.nama}
                                                                </span>
                                                                {row.employee.unit_kerja ? (
                                                                    <div
                                                                        className={`text-xs ${
                                                                            isWinner
                                                                                ? 'text-gray-600 dark:text-gray-400'
                                                                                : 'text-gray-500 dark:text-gray-400'
                                                                        }`}
                                                                    >
                                                                        {row.employee.unit_kerja}
                                                                    </div>
                                                                ) : null}
                                                            </td>
                                                            <td
                                                                className={`px-4 py-3 text-sm ${
                                                                    isWinner
                                                                        ? 'text-gray-700 dark:text-gray-300'
                                                                        : 'text-gray-600 dark:text-gray-400'
                                                                }`}
                                                            >
                                                                {row.employee.nip || '-'}
                                                            </td>
                                                            <td
                                                                className={`px-4 py-3 text-sm ${
                                                                    isWinner
                                                                        ? 'text-gray-700 dark:text-gray-300'
                                                                        : 'text-gray-600 dark:text-gray-400'
                                                                }`}
                                                            >
                                                                {row.employee.jabatan || '-'}
                                                            </td>
                                                            <td
                                                                className={`px-4 py-3 text-right text-sm font-medium ${
                                                                    isWinner
                                                                        ? 'text-gray-900 dark:text-yellow-400'
                                                                        : 'text-gray-900 dark:text-gray-100'
                                                                }`}
                                                            >
                                                                {row.total_score.toFixed(2)}
                                                            </td>
                                                            <td
                                                                className={`px-4 py-3 text-right text-sm ${
                                                                    isWinner
                                                                        ? 'text-gray-700 dark:text-gray-300'
                                                                        : 'text-gray-600 dark:text-gray-400'
                                                                }`}
                                                            >
                                                                {row.average_score.toFixed(2)}
                                                            </td>
                                                            <td
                                                                className={`px-4 py-3 text-right text-sm ${
                                                                    isWinner
                                                                        ? 'text-gray-700 dark:text-gray-300'
                                                                        : 'text-gray-600 dark:text-gray-400'
                                                                }`}
                                                            >
                                                                {row.votes_count}
                                                            </td>
                                                        </tr>
                                                        );
                                                    })}
                                                </tbody>
                                            </table>
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    </>
                )}
            </div>
        </AppLayout>
    );
}
