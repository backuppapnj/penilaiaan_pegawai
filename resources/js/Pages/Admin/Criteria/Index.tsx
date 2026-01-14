import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Edit, Eye, Plus, Trash2 } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Kriteria', href: '/admin/criteria' },
];

interface Category {
    id: number;
    nama: string;
    urutan: number;
}

interface Criterion {
    id: number;
    nama: string;
    bobot: number;
    urutan: number;
    category_id: number;
    category: Category;
}

interface PageProps {
    criteria: Criterion[];
    categories: Category[];
}

export default function CriteriaIndex({ criteria, categories }: PageProps) {
    const handleDelete = (id: number) => {
        if (confirm('Apakah Anda yakin ingin menghapus kriteria ini?')) {
            router.delete(`/admin/criteria/${id}`);
        }
    };

    const updateWeight = (id: number, weight: number) => {
        router.post(
            `/admin/criteria/${id}/weight`,
            { bobot: weight },
            {
                onSuccess: () => {
                    // Success message will be shown by backend
                },
            },
        );
    };

    // Group criteria by category
    const groupedCriteria = categories.reduce(
        (acc, category) => {
            acc[category.id] = criteria.filter(
                (c) => c.category_id === category.id,
            );
            return acc;
        },
        {} as Record<number, Criterion[]>,
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Kelola Kriteria" />

            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
                <div className="flex items-center justify-between">
                    <div className="space-y-2">
                        <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                            Kelola Kriteria Penilaian
                        </h1>
                        <p className="text-muted-foreground">
                            Atur kriteria dan bobot penilaian untuk setiap
                            kategori
                        </p>
                    </div>
                    <Link
                        href="/admin/criteria/create"
                        className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        <Plus className="size-4" />
                        Tambah Kriteria
                    </Link>
                </div>

                <div className="space-y-6">
                    {categories.map((category) => {
                        const categoryCriteria =
                            groupedCriteria[category.id] || [];

                        return (
                            <div
                                key={category.id}
                                className="rounded-xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-gray-800/50"
                            >
                                <div className="border-b border-sidebar-border/70 bg-gray-50 px-6 py-4 dark:border-sidebar-border dark:bg-gray-800/30">
                                    <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {category.nama}
                                    </h2>
                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                        {categoryCriteria.length} kriteria
                                    </p>
                                </div>

                                {categoryCriteria.length === 0 ? (
                                    <div className="p-6 text-center">
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            Belum ada kriteria untuk kategori
                                            ini
                                        </p>
                                        <Link
                                            href="/admin/criteria/create"
                                            className="mt-2 inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
                                        >
                                            <Plus className="size-4" />
                                            Tambah Kriteria
                                        </Link>
                                    </div>
                                ) : (
                                    <div className="overflow-x-auto">
                                        <table className="w-full">
                                            <thead>
                                                <tr className="border-b border-sidebar-border/70 bg-gray-50 dark:border-sidebar-border dark:bg-gray-800/30">
                                                    <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase dark:text-gray-300">
                                                        Urutan
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase dark:text-gray-300">
                                                        Nama Kriteria
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-700 uppercase dark:text-gray-300">
                                                        Bobot (%)
                                                    </th>
                                                    <th className="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-700 uppercase dark:text-gray-300">
                                                        Aksi
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                                                {categoryCriteria.map(
                                                    (criterion) => (
                                                        <tr
                                                            key={criterion.id}
                                                            className="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/30"
                                                        >
                                                            <td className="px-6 py-4 whitespace-nowrap">
                                                                <span className="inline-flex items-center justify-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                                    {
                                                                        criterion.urutan
                                                                    }
                                                                </span>
                                                            </td>
                                                            <td className="px-6 py-4">
                                                                <div className="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                    {
                                                                        criterion.nama
                                                                    }
                                                                </div>
                                                            </td>
                                                            <td className="px-6 py-4 whitespace-nowrap">
                                                                <input
                                                                    type="number"
                                                                    value={
                                                                        criterion.bobot
                                                                    }
                                                                    onChange={(
                                                                        e,
                                                                    ) => {
                                                                        const newWeight =
                                                                            parseFloat(
                                                                                e
                                                                                    .target
                                                                                    .value,
                                                                            );
                                                                        if (
                                                                            newWeight >=
                                                                                0 &&
                                                                            newWeight <=
                                                                                100
                                                                        ) {
                                                                            updateWeight(
                                                                                criterion.id,
                                                                                newWeight,
                                                                            );
                                                                        }
                                                                    }}
                                                                    min="0"
                                                                    max="100"
                                                                    step="0.01"
                                                                    className="w-24 rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                                />
                                                            </td>
                                                            <td className="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                                                <div className="flex items-center justify-end gap-2">
                                                                    <Link
                                                                        href={`/admin/criteria/${criterion.id}`}
                                                                        className="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                                                        aria-label="Lihat detail"
                                                                    >
                                                                        <Eye className="size-3" />
                                                                        Detail
                                                                    </Link>
                                                                    <Link
                                                                        href={`/admin/criteria/${criterion.id}/edit`}
                                                                        className="inline-flex items-center gap-1 rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 transition-colors hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50"
                                                                        aria-label="Edit"
                                                                    >
                                                                        <Edit className="size-3" />
                                                                        Edit
                                                                    </Link>
                                                                    <button
                                                                        onClick={() =>
                                                                            handleDelete(
                                                                                criterion.id,
                                                                            )
                                                                        }
                                                                        className="inline-flex items-center gap-1 rounded-lg bg-red-100 px-3 py-1.5 text-xs font-medium text-red-700 transition-colors hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                                                                        aria-label="Hapus"
                                                                    >
                                                                        <Trash2 className="size-3" />
                                                                        Hapus
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    ),
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </div>
                        );
                    })}
                </div>

                <div className="flex items-center justify-between">
                    <Link
                        href="/admin"
                        className="inline-flex items-center gap-2 text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        <ArrowLeft className="size-4" />
                        Kembali ke Dashboard
                    </Link>
                    {criteria.length > 0 && (
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            Total {criteria.length} kriteria
                        </p>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
