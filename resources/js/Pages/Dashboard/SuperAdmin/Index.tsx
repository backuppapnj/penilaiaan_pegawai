import ActivityList from '@/components/dashboard/activity-list';
import StatCard from '@/components/dashboard/stat-card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { Award, FileText, Users, Vote } from 'lucide-react';

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/super-admin',
    },
];

interface PageProps {
    stats: {
        total_employees: number;
        active_periods: number;
        total_votes: number;
        certificates_generated: number;
        category_1_count: number;
        category_2_count: number;
    };
    activityLogs: Array<{
        id: number;
        action: string;
        description: string;
        user: string;
        created_at: string;
    }>;
}

export default function SuperAdminDashboard({
    stats,
    activityLogs,
}: PageProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Super Admin Dashboard" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div className="space-y-2">
                        <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                            Super Admin Dashboard
                        </h1>
                        <p className="text-muted-foreground">
                            Selamat datang di panel administrasi sistem penilaian
                            pegawai.
                        </p>
                    </div>
                    {stats.active_periods > 0 && (
                        <div className="flex flex-col items-start gap-2">
                            <Link
                                href="/penilai/voting"
                                className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                            >
                                <Vote className="size-4" />
                                Mulai Menilai
                            </Link>
                            <p className="text-xs text-gray-600 dark:text-gray-400">
                                Periode aktif tersedia
                            </p>
                        </div>
                    )}
                </div>

                <div className="grid auto-rows-min gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <StatCard
                        title="Total Pegawai"
                        value={stats.total_employees}
                        description="Pegawai terdaftar"
                        gradient="from-red-500 to-red-600"
                        icon={Users}
                    />
                    <StatCard
                        title="Kategori 1"
                        value={stats.category_1_count}
                        description="Pejabat Struktural/Fungsional"
                        gradient="from-blue-500 to-blue-600"
                    />
                    <StatCard
                        title="Kategori 2"
                        value={stats.category_2_count}
                        description="Non-Pejabat"
                        gradient="from-green-500 to-green-600"
                    />
                    <StatCard
                        title="Sertifikat"
                        value={stats.certificates_generated}
                        description="Telah dibuat"
                        gradient="from-purple-500 to-purple-600"
                        icon={Award}
                    />
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    <ActivityList activities={activityLogs} />

                    <div className="space-y-6 lg:col-span-2">
                        <div className="grid gap-4 md:grid-cols-2">
                            <StatCard
                                title="Periode Aktif"
                                value={stats.active_periods}
                                description="Sedang berlangsung"
                                gradient="from-emerald-500 to-emerald-600"
                            />
                            <StatCard
                                title="Total Suara"
                                value={stats.total_votes}
                                description="Telah masuk"
                                gradient="from-cyan-500 to-cyan-600"
                                icon={Vote}
                            />
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <Link
                                href="/admin/periods"
                                className="flex items-center justify-center gap-2 rounded-xl border border-sidebar-border/70 bg-gray-50 p-6 text-center transition-colors hover:bg-gray-100 dark:border-sidebar-border dark:bg-gray-800/50 dark:hover:bg-gray-800"
                            >
                                <div>
                                    <h3 className="font-semibold text-gray-900 dark:text-gray-100">
                                        Kelola Periode
                                    </h3>
                                    <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Buat dan atur periode penilaian
                                    </p>
                                </div>
                            </Link>
                            <Link
                                href="/admin/sikep"
                                className="flex items-center justify-center gap-2 rounded-xl border border-sidebar-border/70 bg-gray-50 p-6 text-center transition-colors hover:bg-gray-100 dark:border-sidebar-border dark:bg-gray-800/50 dark:hover:bg-gray-800"
                            >
                                <div>
                                    <h3 className="font-semibold text-gray-900 dark:text-gray-100">
                                        Import Data SIKEP
                                    </h3>
                                    <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Upload file Excel kehadiran
                                    </p>
                                </div>
                            </Link>
                        </div>

                        <div className="rounded-xl border border-sidebar-border/70 bg-gray-50 p-6 dark:border-sidebar-border dark:bg-gray-800/50">
                            <h3 className="mb-4 font-semibold text-gray-900 dark:text-gray-100">
                                Quick Actions
                            </h3>
                            <div className="flex flex-wrap gap-3">
                                <Link
                                    href="/admin/periods/create"
                                    className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                                >
                                    <FileText className="size-4" />
                                    Buat Periode Baru
                                </Link>
                                <Link
                                    href="/admin/criteria"
                                    className="inline-flex items-center gap-2 rounded-lg bg-gray-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-700"
                                >
                                    Kelola Kriteria
                                </Link>
                                <Link
                                    href="/admin/employees"
                                    className="inline-flex items-center gap-2 rounded-lg bg-gray-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-700"
                                >
                                    Kelola Pegawai
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
