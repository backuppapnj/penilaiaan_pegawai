import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';
import { FormEvent } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Kriteria', href: '/admin/criteria' },
    { title: 'Buat Kriteria', href: '/admin/criteria/create' },
];

interface Category {
    id: number;
    nama: string;
    urutan: number;
}

interface Errors {
    nama?: string[];
    bobot?: string[];
    category_id?: string[];
    urutan?: string[];
}

interface PageProps {
    categories: Category[];
    errors: Errors;
}

export default function CreateCriterion({ categories, errors }: PageProps) {
    const { data, setData, post, processing, recentlySuccessful } = useForm({
        nama: '',
        bobot: 0,
        category_id: '',
        urutan: 1,
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        post('/admin/criteria');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Buat Kriteria Baru" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                <div className="space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                        Buat Kriteria Baru
                    </h1>
                    <p className="text-muted-foreground">
                        Buat kriteria penilaian baru
                    </p>
                </div>

                <div className="max-w-2xl">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:bg-gray-900">
                            <div className="space-y-4">
                                <div>
                                    <label
                                        htmlFor="nama"
                                        className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Nama Kriteria{' '}
                                        <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="nama"
                                        type="text"
                                        value={data.nama}
                                        onChange={(e) =>
                                            setData('nama', e.target.value)
                                        }
                                        className="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400"
                                        placeholder="Contoh: Kinerja Kerja"
                                        required
                                    />
                                    {errors.nama && (
                                        <p className="mt-1 text-sm text-red-600 dark:text-red-400">
                                            {errors.nama[0]}
                                        </p>
                                    )}
                                </div>

                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label
                                            htmlFor="category_id"
                                            className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >
                                            Kategori{' '}
                                            <span className="text-red-500">
                                                *
                                            </span>
                                        </label>
                                        <select
                                            id="category_id"
                                            value={data.category_id}
                                            onChange={(e) =>
                                                setData(
                                                    'category_id',
                                                    e.target.value,
                                                )
                                            }
                                            className="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                            required
                                        >
                                            <option value="">
                                                Pilih Kategori
                                            </option>
                                            {categories.map((category) => (
                                                <option
                                                    key={category.id}
                                                    value={category.id}
                                                >
                                                    {category.nama}
                                                </option>
                                            ))}
                                        </select>
                                        {errors.category_id && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">
                                                {errors.category_id[0]}
                                            </p>
                                        )}
                                    </div>

                                    <div>
                                        <label
                                            htmlFor="urutan"
                                            className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >
                                            Urutan{' '}
                                            <span className="text-red-500">
                                                *
                                            </span>
                                        </label>
                                        <input
                                            id="urutan"
                                            type="number"
                                            value={data.urutan}
                                            onChange={(e) =>
                                                setData(
                                                    'urutan',
                                                    parseInt(e.target.value),
                                                )
                                            }
                                            min="1"
                                            className="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                            required
                                        />
                                        {errors.urutan && (
                                            <p className="mt-1 text-sm text-red-600 dark:text-red-400">
                                                {errors.urutan[0]}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div>
                                    <label
                                        htmlFor="bobot"
                                        className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Bobot (%){' '}
                                        <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="bobot"
                                        type="number"
                                        value={data.bobot}
                                        onChange={(e) =>
                                            setData(
                                                'bobot',
                                                parseFloat(e.target.value),
                                            )
                                        }
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        className="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                        required
                                    />
                                    {errors.bobot && (
                                        <p className="mt-1 text-sm text-red-600 dark:text-red-400">
                                            {errors.bobot[0]}
                                        </p>
                                    )}
                                    <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Bobot harus antara 0-100%
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="flex items-center justify-end gap-3">
                            <Link
                                href="/admin/criteria"
                                className="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                            >
                                Batal
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {processing ? (
                                    <>
                                        <Loader2 className="size-4 animate-spin" />
                                        Menyimpan...
                                    </>
                                ) : (
                                    'Simpan Kriteria'
                                )}
                            </button>
                        </div>

                        {recentlySuccessful && (
                            <div className="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-950">
                                <p className="text-sm font-medium text-green-800 dark:text-green-200">
                                    Kriteria berhasil dibuat!
                                </p>
                            </div>
                        )}
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
