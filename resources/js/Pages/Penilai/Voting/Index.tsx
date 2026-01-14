import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { AlertCircle, CheckCircle2, Clock } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/penilai',
    },
    {
        title: 'Voting',
        href: '/penilai/voting',
    },
];

interface Criterion {
    id: number;
    nama: string;
    bobot: number;
    urutan: number;
}

interface Category {
    id: number;
    nama: string;
    deskripsi: string;
    urutan: number;
    criteria: Criterion[];
}

interface Employee {
    id: number;
    nama: string;
    nip: string;
    category: Category;
}

interface PageProps {
    activePeriod: {
        id: number;
        name: string;
        start_date: string;
        end_date: string;
    } | null;
    categories: Category[];
    employees: Employee[];
    votedEmployees: number[];
}

export default function VotingIndex({
    activePeriod,
    categories,
    employees,
}: PageProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Voting Penilaian" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                <div className="space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                        Voting Penilaian
                    </h1>
                    <p className="text-muted-foreground">
                        Pilih kategori untuk mulai memberikan penilaian pada
                        pegawai.
                    </p>
                </div>

                {!activePeriod ? (
                    <div className="rounded-xl border border-yellow-200 bg-yellow-50 p-8 text-center dark:border-yellow-900 dark:bg-yellow-950">
                        <AlertCircle className="mx-auto mb-4 size-12 text-yellow-600 dark:text-yellow-400" />
                        <h2 className="mb-2 text-xl font-semibold text-yellow-900 dark:text-yellow-100">
                            Tidak Ada Periode Aktif
                        </h2>
                        <p className="text-yellow-800 dark:text-yellow-200">
                            Belum ada periode penilaian yang dibuka. Silakan
                            tunggu pengumuman lebih lanjut.
                        </p>
                    </div>
                ) : (
                    <>
                        <div className="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950">
                            <div className="flex items-center gap-3">
                                <Clock className="size-5 text-blue-600 dark:text-blue-400" />
                                <div>
                                    <h3 className="font-semibold text-blue-900 dark:text-blue-100">
                                        {activePeriod.name}
                                    </h3>
                                    <p className="text-sm text-blue-800 dark:text-blue-200">
                                        {activePeriod.start_date} -{' '}
                                        {activePeriod.end_date}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {categories.length === 0 ? (
                            <div className="rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-gray-800 dark:bg-gray-900">
                                <AlertCircle className="mx-auto mb-4 size-12 text-gray-400" />
                                <h2 className="mb-2 text-xl font-semibold text-gray-900 dark:text-gray-100">
                                    Belum Ada Kategori
                                </h2>
                                <p className="text-gray-600 dark:text-gray-400">
                                    Belum ada kategori penilaian yang tersedia.
                                </p>
                            </div>
                        ) : (
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                {categories.map((category) => {
                                    const categoryEmployees = employees.filter(
                                        (emp) =>
                                            emp.category?.id === category.id,
                                    );
                                    const totalEmployees =
                                        categoryEmployees.length;
                                    const isCompleted = totalEmployees === 0;

                                    return (
                                        <div
                                            key={category.id}
                                            className="group relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-sidebar-border dark:bg-gray-900"
                                        >
                                            <div className="flex items-start justify-between">
                                                <div className="flex-1">
                                                    <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                        {category.nama}
                                                    </h3>
                                                    <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                        {category.deskripsi ||
                                                            'Tidak ada deskripsi'}
                                                    </p>
                                                    <div className="mt-4 flex items-center gap-2 text-sm">
                                                        {isCompleted ? (
                                                            <>
                                                                <CheckCircle2 className="size-4 text-green-600 dark:text-green-400" />
                                                                <span className="text-green-600 dark:text-green-400">
                                                                    Selesai
                                                                    dinilai
                                                                </span>
                                                            </>
                                                        ) : (
                                                            <>
                                                                <Clock className="size-4 text-amber-600 dark:text-amber-400" />
                                                                <span className="text-gray-600 dark:text-gray-400">
                                                                    {
                                                                        totalEmployees
                                                                    }{' '}
                                                                    pegawai
                                                                    belum
                                                                    dinilai
                                                                </span>
                                                            </>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>

                                            <Link
                                                href={`/penilai/voting/${activePeriod.id}/${category.id}`}
                                                className="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                                                aria-label={`Mulai menilai kategori ${category.nama}`}
                                            >
                                                {isCompleted
                                                    ? 'Lihat Hasil'
                                                    : 'Mulai Menilai'}
                                            </Link>
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </>
                )}
            </div>
        </AppLayout>
    );
}
