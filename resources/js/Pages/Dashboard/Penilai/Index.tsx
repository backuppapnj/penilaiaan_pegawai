import StatCard from '@/components/dashboard/stat-card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import {
    AlertCircle,
    Calendar,
    CheckCircle2,
    Clock,
    History,
    Vote,
} from 'lucide-react';

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/penilai',
    },
];

interface CategoryStat {
    id: number;
    name: string;
    description: string;
    completed: number;
    total: number;
    status: 'completed' | 'pending';
    percentage: number;
}

interface RecentVote {
    employee_name: string;
    category: string;
    total_score: number;
    voted_at: string;
}

interface PageProps {
    stats: {
        has_active_period: boolean;
        active_period: {
            id: number;
            name: string;
            start_date: string;
            end_date: string;
        } | null;
        category_stats: CategoryStat[];
        recent_votes: RecentVote[];
    };
}

export default function PenilaiDashboard({ stats }: PageProps) {
    const activePeriod = stats.active_period;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Penilai Dashboard" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                <div className="space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                        Dashboard Penilai
                    </h1>
                    <p className="text-muted-foreground">
                        Berikan penilaian pada pegawai sesuai kriteria yang
                        telah ditentukan.
                    </p>
                </div>

                {!stats.has_active_period || !activePeriod ? (
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
                                <Calendar className="size-5 text-blue-600 dark:text-blue-400" />
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

                        <div className="grid auto-rows-min gap-4 md:grid-cols-2">
                            {stats.category_stats.map((category) => (
                                <StatCard
                                    key={category.id}
                                    title={category.name}
                                    value={`${category.completed}/${category.total}`}
                                    description={`${category.percentage}% selesai · ${category.description}`}
                                    gradient={
                                        category.status === 'completed'
                                            ? 'from-green-500 to-green-600'
                                            : 'from-indigo-500 to-indigo-600'
                                    }
                                    icon={
                                        category.status === 'completed'
                                            ? CheckCircle2
                                            : Clock
                                    }
                                />
                            ))}
                        </div>

                        <div className="grid gap-6 lg:grid-cols-2">
                            <div className="rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-900">
                                <div className="border-b border-gray-200 p-6 dark:border-gray-800">
                                    <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        Mulai Penilaian
                                    </h2>
                                </div>
                                <div className="divide-y divide-gray-200 dark:divide-gray-800">
                                    {stats.category_stats.map((category) => (
                                        <div
                                            key={category.id}
                                            className="flex items-center justify-between p-4 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                        >
                                            <div className="flex-1">
                                                <h3 className="font-medium text-gray-900 dark:text-gray-100">
                                                    {category.name}
                                                </h3>
                                                <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                    {category.description} ·{' '}
                                                    {category.completed}/
                                                    {category.total} telah
                                                    dinilai
                                                </p>
                                            </div>
                                            <Link
                                                href={
                                                    '/penilai/voting/' +
                                                    activePeriod.id +
                                                    '/' +
                                                    category.id
                                                }
                                                className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                                            >
                                                <Vote className="size-4" />
                                                {category.status === 'completed'
                                                    ? 'Lihat Hasil'
                                                    : 'Mulai Menilai'}
                                            </Link>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-900">
                                <div className="flex items-center justify-between border-b border-gray-200 p-6 dark:border-gray-800">
                                    <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        Penilaian Terakhir
                                    </h2>
                                    <Link
                                        href={'/penilai/voting/history'}
                                        className="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                    >
                                        Lihat Semua
                                    </Link>
                                </div>
                                <div className="divide-y divide-gray-200 dark:divide-gray-800">
                                    {stats.recent_votes.length === 0 ? (
                                        <div className="p-8 text-center">
                                            <History className="mx-auto mb-3 size-12 text-gray-400" />
                                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                                Belum ada penilaian yang
                                                dilakukan
                                            </p>
                                        </div>
                                    ) : (
                                        stats.recent_votes.map(
                                            (vote, index) => (
                                                <div
                                                    key={index}
                                                    className="flex items-start gap-3 p-4 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                                >
                                                    <div className="flex size-8 flex-shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                                        <Vote className="size-4 text-gray-600 dark:text-gray-400" />
                                                    </div>
                                                    <div className="min-w-0 flex-1">
                                                        <p className="font-medium text-gray-900 dark:text-gray-100">
                                                            {vote.employee_name}
                                                        </p>
                                                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                            {vote.category} ·
                                                            Skor:{' '}
                                                            {vote.total_score}
                                                        </p>
                                                        <p className="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                                            {vote.voted_at}
                                                        </p>
                                                    </div>
                                                </div>
                                            ),
                                        )
                                    )}
                                </div>
                            </div>
                        </div>

                        <div className="rounded-xl border border-sidebar-border/70 bg-gray-50 p-6 dark:border-sidebar-border dark:bg-gray-800/50">
                            <h3 className="mb-3 font-semibold text-gray-900 dark:text-gray-100">
                                Informasi Penting
                            </h3>
                            <ul className="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                <li className="flex items-start gap-2">
                                    <CheckCircle2 className="mt-0.5 size-4 flex-shrink-0 text-green-600 dark:text-green-400" />
                                    <span>
                                        Nilai semua pegawai dengan objektif dan
                                        jujur
                                    </span>
                                </li>
                                <li className="flex items-start gap-2">
                                    <CheckCircle2 className="mt-0.5 size-4 flex-shrink-0 text-green-600 dark:text-green-400" />
                                    <span>
                                        Periode penilaian berakhir pada{' '}
                                        {activePeriod.end_date}
                                    </span>
                                </li>
                                <li className="flex items-start gap-2">
                                    <CheckCircle2 className="mt-0.5 size-4 flex-shrink-0 text-green-600 dark:text-green-400" />
                                    <span>
                                        Anda dapat melihat riwayat penilaian di
                                        menu History
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </>
                )}
            </div>
        </AppLayout>
    );
}
