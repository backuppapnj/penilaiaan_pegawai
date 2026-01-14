import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, CalendarClock } from 'lucide-react';
import { FormEvent } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Periode', href: '/admin/periods' },
    { title: 'Buat Periode Baru', href: '/admin/periods/create' },
];

export default function CreatePeriod() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        semester: 'ganjil',
        year: new Date().getFullYear(),
        start_date: '',
        end_date: '',
        notes: '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        post('/admin/periods');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Buat Periode Baru" />

            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
                <div className="space-y-2">
                    <div className="flex items-center gap-4">
                        <Link
                            href="/admin/periods"
                            className="inline-flex items-center gap-2 text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                        >
                            <ArrowLeft className="size-4" />
                            Kembali
                        </Link>
                    </div>
                    <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                        Buat Periode Baru
                    </h1>
                    <p className="text-muted-foreground">
                        Buat periode penilaian dan voting baru
                    </p>
                </div>

                <div className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800/50">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="grid gap-6 md:grid-cols-2">
                            <div className="space-y-2">
                                <label
                                    htmlFor="name"
                                    className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Nama Periode{' '}
                                    <span className="text-red-500">*</span>
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    value={data.name}
                                    onChange={(e) =>
                                        setData('name', e.target.value)
                                    }
                                    placeholder="Contoh: Penilaian Semester 1 2024"
                                    className="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    required
                                />
                                {errors.name && (
                                    <p className="text-sm text-red-600 dark:text-red-400">
                                        {errors.name}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <label
                                    htmlFor="semester"
                                    className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Semester{' '}
                                    <span className="text-red-500">*</span>
                                </label>
                                <select
                                    id="semester"
                                    value={data.semester}
                                    onChange={(e) =>
                                        setData('semester', e.target.value)
                                    }
                                    className="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    required
                                >
                                    <option value="ganjil">Ganjil</option>
                                    <option value="genap">Genap</option>
                                </select>
                                {errors.semester && (
                                    <p className="text-sm text-red-600 dark:text-red-400">
                                        {errors.semester}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <label
                                    htmlFor="year"
                                    className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Tahun{' '}
                                    <span className="text-red-500">*</span>
                                </label>
                                <input
                                    id="year"
                                    type="number"
                                    value={data.year}
                                    onChange={(e) =>
                                        setData(
                                            'year',
                                            parseInt(e.target.value),
                                        )
                                    }
                                    min="2020"
                                    max="2100"
                                    className="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    required
                                />
                                {errors.year && (
                                    <p className="text-sm text-red-600 dark:text-red-400">
                                        {errors.year}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <label
                                    htmlFor="notes"
                                    className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Catatan
                                </label>
                                <input
                                    id="notes"
                                    type="text"
                                    value={data.notes}
                                    onChange={(e) =>
                                        setData('notes', e.target.value)
                                    }
                                    placeholder="Catatan tambahan (opsional)"
                                    className="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                />
                                {errors.notes && (
                                    <p className="text-sm text-red-600 dark:text-red-400">
                                        {errors.notes}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <label
                                    htmlFor="start_date"
                                    className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Tanggal Mulai
                                </label>
                                <input
                                    id="start_date"
                                    type="date"
                                    value={data.start_date}
                                    onChange={(e) =>
                                        setData('start_date', e.target.value)
                                    }
                                    className="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                />
                                {errors.start_date && (
                                    <p className="text-sm text-red-600 dark:text-red-400">
                                        {errors.start_date}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <label
                                    htmlFor="end_date"
                                    className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Tanggal Selesai
                                </label>
                                <input
                                    id="end_date"
                                    type="date"
                                    value={data.end_date}
                                    onChange={(e) =>
                                        setData('end_date', e.target.value)
                                    }
                                    min={data.start_date}
                                    className="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                />
                                {errors.end_date && (
                                    <p className="text-sm text-red-600 dark:text-red-400">
                                        {errors.end_date}
                                    </p>
                                )}
                            </div>
                        </div>

                        <div className="flex items-center justify-end gap-4 border-t border-gray-200 pt-6 dark:border-gray-700">
                            <Link
                                href="/admin/periods"
                                className="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                            >
                                Batal
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <CalendarClock className="size-4" />
                                {processing ? 'Menyimpan...' : 'Buat Periode'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
