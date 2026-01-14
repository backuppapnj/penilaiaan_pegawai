import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Kriteria', href: '/admin/criteria' },
];

interface Category {
    id: number;
    nama: string;
    urutan: number;
}

interface VoteDetail {
    id: number;
    score: number;
    voter: {
        nama: string;
        nip: string;
    };
}

interface Criterion {
    id: number;
    nama: string;
    bobot: number;
    urutan: number;
    category: Category;
    voteDetails: VoteDetail[];
}

interface PageProps {
    criterion: Criterion;
}

export default function ShowCriterion({ criterion }: PageProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail: ${criterion.nama}`} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                <div className="flex items-center justify-between">
                    <div className="space-y-2">
                        <Link
                            href="/admin/criteria"
                            className="inline-flex items-center gap-2 text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                        >
                            <ArrowLeft className="size-4" />
                            Kembali ke Daftar
                        </Link>
                        <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                            {criterion.nama}
                        </h1>
                        <p className="text-muted-foreground">
                            Kriteria untuk {criterion.category.nama}
                        </p>
                    </div>
                    <Link
                        href={`/admin/criteria/${criterion.id}/edit`}
                        className="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        <Edit className="size-4" />
                        Edit
                    </Link>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    <div className="space-y-6 lg:col-span-2">
                        <div className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:bg-gray-900">
                            <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Informasi Kriteria
                            </h2>
                            <dl className="space-y-4">
                                <div className="grid grid-cols-3 gap-4">
                                    <dt className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Nama
                                    </dt>
                                    <dd className="col-span-2 text-sm text-gray-900 dark:text-gray-100">
                                        {criterion.nama}
                                    </dd>
                                </div>
                                <div className="grid grid-cols-3 gap-4">
                                    <dt className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Kategori
                                    </dt>
                                    <dd className="col-span-2 text-sm text-gray-900 dark:text-gray-100">
                                        {criterion.category.nama}
                                    </dd>
                                </div>
                                <div className="grid grid-cols-3 gap-4">
                                    <dt className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Urutan
                                    </dt>
                                    <dd className="col-span-2 text-sm text-gray-900 dark:text-gray-100">
                                        {criterion.urutan}
                                    </dd>
                                </div>
                                <div className="grid grid-cols-3 gap-4">
                                    <dt className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Bobot
                                    </dt>
                                    <dd className="col-span-2 text-sm text-gray-900 dark:text-gray-100">
                                        {criterion.bobot}%
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        {criterion.voteDetails.length > 0 && (
                            <div className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:bg-gray-900">
                                <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Detail Voting
                                </h2>
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead>
                                            <tr className="border-b border-gray-200 dark:border-gray-800">
                                                <th className="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                    Pemilih
                                                </th>
                                                <th className="px-4 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                    Skor
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                                            {criterion.voteDetails.map(
                                                (voteDetail) => (
                                                    <tr
                                                        key={voteDetail.id}
                                                        className="hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                                    >
                                                        <td className="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                            {
                                                                voteDetail.voter
                                                                    .nama
                                                            }
                                                            <div className="text-xs text-gray-500 dark:text-gray-400">
                                                                {
                                                                    voteDetail
                                                                        .voter
                                                                        .nip
                                                                }
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            {voteDetail.score}
                                                        </td>
                                                    </tr>
                                                ),
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}
                    </div>

                    <div className="space-y-6">
                        <div className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:bg-gray-900">
                            <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Statistik
                            </h2>
                            <dl className="space-y-4">
                                <div className="flex items-center justify-between">
                                    <dt className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Total Voting
                                    </dt>
                                    <dd className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        {criterion.voteDetails.length}
                                    </dd>
                                </div>
                                {criterion.voteDetails.length > 0 && (
                                    <>
                                        <div className="flex items-center justify-between">
                                            <dt className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                                Rata-rata Skor
                                            </dt>
                                            <dd className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                {(
                                                    criterion.voteDetails.reduce(
                                                        (sum, vd) =>
                                                            sum + vd.score,
                                                        0,
                                                    ) /
                                                    criterion.voteDetails.length
                                                ).toFixed(2)}
                                            </dd>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <dt className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                                Skor Tertinggi
                                            </dt>
                                            <dd className="text-2xl font-bold text-green-600 dark:text-green-400">
                                                {Math.max(
                                                    ...criterion.voteDetails.map(
                                                        (vd) => vd.score,
                                                    ),
                                                )}
                                            </dd>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <dt className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                                Skor Terendah
                                            </dt>
                                            <dd className="text-2xl font-bold text-red-600 dark:text-red-400">
                                                {Math.min(
                                                    ...criterion.voteDetails.map(
                                                        (vd) => vd.score,
                                                    ),
                                                )}
                                            </dd>
                                        </div>
                                    </>
                                )}
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
