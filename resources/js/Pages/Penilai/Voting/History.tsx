import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { AlertCircle, Calendar, CheckCircle2, User } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/penilai',
    },
    {
        title: 'Voting',
        href: '/penilai/voting',
    },
    {
        title: 'History',
        href: '/penilai/voting/history',
    },
];

interface VoteDetail {
    id: number;
    vote_id: number;
    criterion_id: number;
    score: number;
    criterion: {
        id: number;
        nama: string;
        bobot: number;
        urutan: number;
    };
}

interface Vote {
    id: number;
    period_id: number;
    employee_id: number;
    category_id: number;
    voter_id: number;
    total_score: number;
    created_at: string;
    updated_at: string;
    period: {
        id: number;
        name: string;
        start_date: string;
        end_date: string;
    };
    employee: {
        id: number;
        nama: string;
        nip: string;
        jabatan?: string;
    };
    category: {
        id: number;
        nama: string;
        deskripsi: string;
        urutan: number;
    };
    voteDetails?: VoteDetail[];
    vote_details?: VoteDetail[];
}

interface PaginationLink {
    label: string;
    url: string | null;
    active: boolean;
}

interface PaginatedData {
    current_page: number;
    data: Vote[];
    from: number;
    last_page: number;
    links: PaginationLink[];
    next_page_url: string | null;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

interface PageProps {
    votes: PaginatedData;
}

export default function VotingHistory({ votes }: PageProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Riwayat Penilaian" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                <div className="space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                        Riwayat Penilaian
                    </h1>
                    <p className="text-muted-foreground">
                        Lihat semua penilaian yang telah Anda berikan pada
                        pegawai.
                    </p>
                </div>

                <div className="flex items-center justify-end">
                    <Link
                        href="/penilai/voting"
                        className="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        Kembali ke Voting
                    </Link>
                </div>

                {votes.data.length === 0 ? (
                    <div className="rounded-xl border border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-gray-900">
                        <AlertCircle className="mx-auto mb-4 size-12 text-gray-400" />
                        <h2 className="mb-2 text-xl font-semibold text-gray-900 dark:text-gray-100">
                            Belum Ada Penilaian
                        </h2>
                        <p className="text-gray-600 dark:text-gray-400">
                            Anda belum memberikan penilaian pada pegawai
                            manapun.
                        </p>
                        <Link
                            href="/penilai/voting"
                            className="mt-4 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                        >
                            Mulai Menilai
                        </Link>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {votes.data.map((vote) => {
                            const voteDetails =
                                vote.voteDetails ?? vote.vote_details ?? [];

                            return (
                            <div
                                key={vote.id}
                                className="rounded-xl border border-sidebar-border/70 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-sidebar-border dark:bg-gray-900"
                            >
                                <div className="mb-4 flex items-start justify-between">
                                    <div className="flex-1">
                                        <div className="mb-2 flex items-center gap-3">
                                            <User className="size-5 text-blue-600 dark:text-blue-400" />
                                            <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                {vote.employee.nama}
                                            </h3>
                                        </div>
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            NIP: {vote.employee.nip}
                                            {vote.employee.jabatan &&
                                                ` · ${vote.employee.jabatan}`}
                                        </p>
                                    </div>
                                    <div className="text-right">
                                        <p className="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                            {vote.total_score}
                                        </p>
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            Total Skor
                                        </p>
                                    </div>
                                </div>

                                <div className="mb-4 grid gap-4 md:grid-cols-2">
                                    <div className="flex items-center gap-2 rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                                        <Calendar className="size-4 text-gray-600 dark:text-gray-400" />
                                        <div>
                                            <p className="text-xs text-gray-600 dark:text-gray-400">
                                                Periode
                                            </p>
                                            <p className="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {vote.period.name}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2 rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                                        <CheckCircle2 className="size-4 text-gray-600 dark:text-gray-400" />
                                        <div>
                                            <p className="text-xs text-gray-600 dark:text-gray-400">
                                                Kategori
                                            </p>
                                            <p className="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {vote.category.nama}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div className="border-t border-gray-200 pt-4 dark:border-gray-800">
                                    <p className="mb-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        Rincian Nilai
                                    </p>
                                    <div className="grid gap-2 md:grid-cols-2 lg:grid-cols-3">
                                        {voteDetails.length > 0 ? (
                                            voteDetails.map((detail) => (
                                                <div
                                                    key={detail.id}
                                                    className="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-800 dark:bg-gray-800"
                                                >
                                                    <div className="flex-1">
                                                        <p className="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            {
                                                                detail.criterion
                                                                    .nama
                                                            }
                                                        </p>
                                                        <p className="text-xs text-gray-600 dark:text-gray-400">
                                                            Bobot:{' '}
                                                            {
                                                                detail.criterion
                                                                    .bobot
                                                            }
                                                            %
                                                        </p>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <div className="h-2 w-24 rounded-full bg-gray-200 dark:bg-gray-700">
                                                            <div
                                                                className="h-2 rounded-full bg-blue-600 dark:bg-blue-400"
                                                                style={{
                                                                    width: `${detail.score}%`,
                                                                }}
                                                            />
                                                        </div>
                                                        <span className="min-w-[3rem] text-right text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                            {detail.score}
                                                        </span>
                                                    </div>
                                                </div>
                                            ))
                                        ) : (
                                            <div className="rounded-lg border border-dashed border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                                Rincian nilai belum tersedia.
                                            </div>
                                        )}
                                    </div>
                                </div>

                                <div className="mt-4 border-t border-gray-200 pt-4 dark:border-gray-800">
                                    <p className="text-xs text-gray-600 dark:text-gray-400">
                                        Dinilai pada{' '}
                                        {new Date(
                                            vote.created_at,
                                        ).toLocaleDateString('id-ID', {
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                        })}
                                    </p>
                                </div>
                            </div>
                            );
                        })}
                    </div>
                )}

                {votes.last_page > 1 && (
                    <div className="flex items-center justify-between rounded-xl border border-sidebar-border/70 bg-white px-6 py-4 dark:border-sidebar-border dark:bg-gray-900">
                        <div className="text-sm text-gray-700 dark:text-gray-300">
                            Menampilkan {votes.from} - {votes.to} dari{' '}
                            {votes.total} penilaian
                        </div>
                        <div className="flex items-center gap-2">
                            <Link
                                href={votes.prev_page_url || '#'}
                                className={`rounded-lg px-3 py-2 text-sm transition-colors ${
                                    !votes.prev_page_url
                                        ? 'cursor-not-allowed opacity-50'
                                        : 'hover:bg-gray-100 dark:hover:bg-gray-800'
                                }`}
                            >
                                Sebelumnya
                            </Link>
                            <span className="text-sm text-gray-700 dark:text-gray-300">
                                Halaman {votes.current_page} dari{' '}
                                {votes.last_page}
                            </span>
                            <Link
                                href={votes.next_page_url || '#'}
                                className={`rounded-lg px-3 py-2 text-sm transition-colors ${
                                    !votes.next_page_url
                                        ? 'cursor-not-allowed opacity-50'
                                        : 'hover:bg-gray-100 dark:hover:bg-gray-800'
                                }`}
                            >
                                Berikutnya
                            </Link>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
