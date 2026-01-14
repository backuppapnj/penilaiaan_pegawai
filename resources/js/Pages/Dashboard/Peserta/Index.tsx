import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { AlertCircle, Award, Download, Trophy, User } from 'lucide-react';

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/peserta',
    },
];

interface Certificate {
    id: number;
    certificate_id: string;
    period: string;
    category: string;
    rank: number;
    issued_at: string;
    download_url: string;
}

interface MyRanking {
    category: string;
    rank: number;
    score: number;
}

interface PageProps {
    stats: {
        profile: {
            nama: string;
            nip: string;
            jabatan: string;
            unit_kerja: string;
            kategori: string;
        } | null;
        has_announced_period: boolean;
        announced_period: {
            id: number;
            name: string;
            year: string;
        } | null;
        my_rankings: MyRanking[];
        my_certificates: Certificate[];
    };
}

export default function PesertaDashboard({ stats }: PageProps) {
    const announcedPeriod = stats.announced_period;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Peserta Dashboard" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                <div className="space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                        Dashboard Peserta
                    </h1>
                    <p className="text-muted-foreground">
                        Lihat hasil penilaian dan peringkat Anda setelah
                        pengumuman.
                    </p>
                </div>

                {stats.profile && (
                    <div className="rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-900">
                        <div className="border-b border-gray-200 p-6 dark:border-gray-800">
                            <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Profil Anda
                            </h2>
                        </div>
                        <div className="divide-y divide-gray-200 dark:divide-gray-800">
                            <div className="flex items-center gap-3 p-4">
                                <div className="flex size-10 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                    <User className="size-5 text-gray-600 dark:text-gray-400" />
                                </div>
                                <div>
                                    <h3 className="font-medium text-gray-900 dark:text-gray-100">
                                        {stats.profile.nama}
                                    </h3>
                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                        {stats.profile.nip}
                                    </p>
                                </div>
                            </div>
                            <div className="grid gap-4 p-4 md:grid-cols-2">
                                <div>
                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                        Jabatan
                                    </p>
                                    <p className="font-medium text-gray-900 dark:text-gray-100">
                                        {stats.profile.jabatan}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                        Unit Kerja
                                    </p>
                                    <p className="font-medium text-gray-900 dark:text-gray-100">
                                        {stats.profile.unit_kerja}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                        Kategori
                                    </p>
                                    <p className="font-medium text-gray-900 dark:text-gray-100">
                                        {stats.profile.kategori}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {!stats.has_announced_period || !announcedPeriod ? (
                    <div className="rounded-xl border border-yellow-200 bg-yellow-50 p-8 text-center dark:border-yellow-900 dark:bg-yellow-950">
                        <AlertCircle className="mx-auto mb-4 size-12 text-yellow-600 dark:text-yellow-400" />
                        <h2 className="mb-2 text-xl font-semibold text-yellow-900 dark:text-yellow-100">
                            Hasil Belum Diumumkan
                        </h2>
                        <p className="text-yellow-800 dark:text-yellow-200">
                            Hasil penilaian belum diumumkan. Silakan tunggu
                            pengumuman dari admin.
                        </p>
                    </div>
                ) : (
                    <>
                        <div className="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950">
                            <div className="flex items-center gap-3">
                                <Trophy className="size-5 text-blue-600 dark:text-blue-400" />
                                <div>
                                    <h3 className="font-semibold text-blue-900 dark:text-blue-100">
                                        Periode {announcedPeriod.name} -{' '}
                                        {announcedPeriod.year}
                                    </h3>
                                    <p className="text-sm text-blue-800 dark:text-blue-200">
                                        Hasil telah diumumkan. Selamat kepada
                                        para pemenang!
                                    </p>
                                </div>
                            </div>
                        </div>

                        {stats.my_rankings.length > 0 && (
                            <div className="rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-900">
                                <div className="border-b border-gray-200 p-6 dark:border-gray-800">
                                    <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        Peringkat Anda
                                    </h2>
                                </div>
                                <div className="divide-y divide-gray-200 dark:divide-gray-800">
                                    {stats.my_rankings.map((ranking, index) => (
                                        <div
                                            key={index}
                                            className="flex items-center justify-between p-4 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                        >
                                            <div className="flex items-center gap-3">
                                                <div
                                                    className={`flex size-10 items-center justify-center rounded-full ${
                                                        ranking.rank === 1
                                                            ? 'bg-yellow-100 dark:bg-yellow-900'
                                                            : ranking.rank === 2
                                                              ? 'bg-gray-100 dark:bg-gray-800'
                                                              : ranking.rank ===
                                                                  3
                                                                ? 'bg-orange-100 dark:bg-orange-900'
                                                                : 'bg-gray-50 dark:bg-gray-800'
                                                    }`}
                                                >
                                                    <span className="text-sm font-bold">
                                                        {ranking.rank === 1
                                                            ? '🥇'
                                                            : ranking.rank === 2
                                                              ? '🥈'
                                                              : ranking.rank ===
                                                                  3
                                                                ? '🥉'
                                                                : `#${ranking.rank}`}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h3 className="font-medium text-gray-900 dark:text-gray-100">
                                                        {ranking.category}
                                                    </h3>
                                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                                        Skor: {ranking.score}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        {stats.my_certificates.length > 0 && (
                            <div className="rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-900">
                                <div className="flex items-center justify-between border-b border-gray-200 p-6 dark:border-gray-800">
                                    <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        Sertifikat Anda
                                    </h2>
                                    <Link
                                        href={'/peserta/sertifikat'}
                                        className="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                    >
                                        Lihat Semua
                                    </Link>
                                </div>
                                <div className="divide-y divide-gray-200 dark:divide-gray-800">
                                    {stats.my_certificates.map((cert) => (
                                        <div
                                            key={cert.id}
                                            className="flex items-center justify-between p-4 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                        >
                                            <div className="flex items-center gap-3">
                                                <div className="rounded-lg bg-yellow-100 p-2 dark:bg-yellow-900">
                                                    <Award className="size-5 text-yellow-600 dark:text-yellow-400" />
                                                </div>
                                                <div>
                                                    <h3 className="font-medium text-gray-900 dark:text-gray-100">
                                                        {cert.period} -{' '}
                                                        {cert.category}
                                                    </h3>
                                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                                        Peringkat {cert.rank} ·{' '}
                                                        {cert.issued_at}
                                                    </p>
                                                </div>
                                            </div>
                                            <a
                                                href={cert.download_url}
                                                className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                                            >
                                                <Download className="size-4" />
                                                Download
                                            </a>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        {stats.my_certificates.length === 0 && (
                            <div className="rounded-xl border border-gray-200 bg-gray-50 p-8 text-center dark:border-gray-800 dark:bg-gray-800/50">
                                <Award className="mx-auto mb-3 size-12 text-gray-400" />
                                <h3 className="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Belum Ada Sertifikat
                                </h3>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    Anda belum memiliki sertifikat. Terus
                                    tingkatkan kinerja Anda!
                                </p>
                            </div>
                        )}
                    </>
                )}
            </div>
        </AppLayout>
    );
}
