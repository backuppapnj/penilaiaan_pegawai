import StatCard from '@/components/dashboard/stat-card';
import StatusBadge from '@/components/dashboard/status-badge';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { Award, Calendar, FileText, Upload, Users, Vote } from 'lucide-react';

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/admin',
    },
];

interface Period {
    id: number;
    name: string;
    status: 'draft' | 'open' | 'closed' | 'announced';
    votes_count: number;
    start_date: string;
    end_date: string;
}

interface PageProps {
    stats: {
        periods: Period[];
        category_1_count: number;
        category_2_count: number;
        voting_progress: {
            total_voters: number;
            votes_cast: number;
            percentage: number;
        } | null;
        has_active_period: boolean;
    };
}

export default function AdminDashboard({ stats }: PageProps) {
    const activePeriod =
        stats.periods.find((period) => period.status === 'open') ?? null;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Dashboard" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div className="space-y-2">
                        <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                            Admin Dashboard
                        </h1>
                        <p className="text-muted-foreground">
                            Kelola periode penilaian, import data, dan verifikasi
                            hasil.
                        </p>
                    </div>
                    {stats.has_active_period && activePeriod && (
                        <div className="flex flex-col items-start gap-2">
                            <Link
                                href="/penilai/voting"
                                className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                            >
                                <Vote className="size-4" />
                                Mulai Menilai
                            </Link>
                            <p className="text-xs text-gray-600 dark:text-gray-400">
                                Periode aktif: {activePeriod.name}
                            </p>
                        </div>
                    )}
                </div>

                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <StatCard
                        title="Kategori 1"
                        value={stats.category_1_count}
                        description="Pejabat Struktural/Fungsional"
                        gradient="from-blue-500 to-blue-600"
                        icon={Users}
                    />
                    <StatCard
                        title="Kategori 2"
                        value={stats.category_2_count}
                        description="Non-Pejabat"
                        gradient="from-green-500 to-green-600"
                        icon={Users}
                    />
                    {stats.voting_progress ? (
                        <StatCard
                            title="Progress Voting"
                            value={`${stats.voting_progress.percentage}%`}
                            description={`${stats.voting_progress.votes_cast} dari ${stats.voting_progress.total_voters} pemilih`}
                            gradient="from-purple-500 to-purple-600"
                        />
                    ) : (
                        <StatCard
                            title="Status Periode"
                            value="Tidak Aktif"
                            description="Tidak ada periode aktif"
                            gradient="from-gray-500 to-gray-600"
                            icon={Calendar}
                        />
                    )}
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <div className="rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-900">
                        <div className="flex items-center justify-between border-b border-gray-200 p-6 dark:border-gray-800">
                            <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Daftar Periode
                            </h2>
                            <Link
                                href={'/admin/periods/create'}
                                className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                            >
                                <FileText className="size-4" />
                                Buat Baru
                            </Link>
                        </div>
                        <div className="divide-y divide-gray-200 dark:divide-gray-800">
                            {stats.periods.length === 0 ? (
                                <div className="p-8 text-center">
                                    <Calendar className="mx-auto mb-3 size-12 text-gray-400" />
                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                        Belum ada periode yang dibuat
                                    </p>
                                </div>
                            ) : (
                                stats.periods.map((period) => (
                                    <div
                                        key={period.id}
                                        className="flex items-center justify-between p-4 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                    >
                                        <div className="flex-1">
                                            <div className="flex items-center gap-3">
                                                <h3 className="font-medium text-gray-900 dark:text-gray-100">
                                                    {period.name}
                                                </h3>
                                                <StatusBadge
                                                    status={period.status}
                                                />
                                            </div>
                                            <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                {period.start_date} -{' '}
                                                {period.end_date} ·{' '}
                                                {period.votes_count} suara
                                            </p>
                                        </div>
                                        <Link
                                            href={'/admin/periods/' + period.id}
                                            className="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                        >
                                            Detail
                                        </Link>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>

                    <div className="space-y-4">
                        <div className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:bg-gray-900">
                            <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Quick Actions
                            </h2>
                            <div className="space-y-3">
                                <Link
                                    href={'/admin/periods/create'}
                                    className="flex items-center gap-3 rounded-lg border border-gray-200 p-4 transition-colors hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800"
                                >
                                    <div className="rounded-lg bg-blue-100 p-2 dark:bg-blue-900">
                                        <FileText className="size-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="font-medium text-gray-900 dark:text-gray-100">
                                            Buat Periode Baru
                                        </h3>
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            Mulai periode penilaian
                                        </p>
                                    </div>
                                </Link>
                                <Link
                                    href={'/admin/criteria'}
                                    className="flex items-center gap-3 rounded-lg border border-gray-200 p-4 transition-colors hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800"
                                >
                                    <div className="rounded-lg bg-purple-100 p-2 dark:bg-purple-900">
                                        <Award className="size-5 text-purple-600 dark:text-purple-400" />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="font-medium text-gray-900 dark:text-gray-100">
                                            Kelola Kriteria
                                        </h3>
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            Atur kriteria penilaian
                                        </p>
                                    </div>
                                </Link>
                                <Link
                                    href={'/admin/sikep'}
                                    className="flex items-center gap-3 rounded-lg border border-gray-200 p-4 transition-colors hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800"
                                >
                                    <div className="rounded-lg bg-green-100 p-2 dark:bg-green-900">
                                        <Upload className="size-5 text-green-600 dark:text-green-400" />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="font-medium text-gray-900 dark:text-gray-100">
                                            Import Data SIKEP
                                        </h3>
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            Upload file Excel kehadiran
                                        </p>
                                    </div>
                                </Link>
                            </div>
                        </div>

                        {stats.has_active_period && (
                            <div className="rounded-xl border border-blue-200 bg-blue-50 p-6 dark:border-blue-900 dark:bg-blue-950">
                                <div className="flex items-start gap-3">
                                    <div className="rounded-lg bg-blue-100 p-2 dark:bg-blue-900">
                                        <Calendar className="size-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="font-semibold text-blue-900 dark:text-blue-100">
                                            Periode Aktif
                                        </h3>
                                        <p className="mt-1 text-sm text-blue-800 dark:text-blue-200">
                                            Pemilihan sedang berlangsung. Pantau
                                            progress dan hasil secara berkala.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
