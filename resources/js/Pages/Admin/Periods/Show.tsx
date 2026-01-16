import StatusBadge from '@/components/dashboard/status-badge';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Edit, Loader2, Trophy, Users } from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Periode', href: '/admin/periods' },
];

interface Vote {
    id: number;
    voter: {
        nama: string;
        nip: string;
    };
    employee: {
        nama: string;
        nip: string;
        category: {
            nama: string;
        };
    };
}

interface Score {
    id: number;
    employee: {
        nama: string;
        nip: string;
        category: {
            nama: string;
        };
    };
    total_score: number;
    rank: number;
}

interface Period {
    id: number;
    name: string;
    semester: string;
    year: number;
    start_date: string;
    end_date: string;
    start_date_formatted?: string;
    end_date_formatted?: string;
    status: 'draft' | 'open' | 'closed' | 'announced';
    notes: string | null;
    votes: Vote[];
    scores?: Score[];
}

interface PageProps {
    period: Period;
    pendingVotersByCategory?: PendingCategory[];
}

interface PendingVoter {
    id: number;
    nama: string;
    nip?: string | null;
    completed: number;
    total: number;
    missing: number;
}

interface PendingCategory {
    id: number;
    nama: string;
    pending_count: number;
    pending: PendingVoter[];
}

export default function ShowPeriod({
    period,
    pendingVotersByCategory = [],
}: PageProps) {
    const [updatingStatus, setUpdatingStatus] = useState(false);
    const [statusDialogOpen, setStatusDialogOpen] = useState(false);
    const [pendingStatus, setPendingStatus] = useState<string | null>(null);

    const handleStatusChange = (newStatus: string) => {
        setPendingStatus(newStatus);
        setStatusDialogOpen(true);
    };

    const confirmStatusChange = () => {
        if (!pendingStatus) return;

        setUpdatingStatus(true);
        router.post(
            `/admin/periods/${period.id}/status/${pendingStatus}`,
            {},
            {
                onFinish: () => {
                    setUpdatingStatus(false);
                    setStatusDialogOpen(false);
                    setPendingStatus(null);
                },
            },
        );
    };

    const getStatusLabel = (status: string | null) => {
        const option = statusOptions.find((opt) => opt.value === status);
        return option ? option.label : status;
    };

    const statusOptions = [
        {
            value: 'draft',
            label: 'Draft',
            color: 'bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700',
        },
        {
            value: 'open',
            label: 'Buka Voting',
            color: 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800',
        },
        {
            value: 'closed',
            label: 'Tutup',
            color: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200 dark:bg-yellow-900 dark:text-yellow-200 dark:hover:bg-yellow-800',
        },
        {
            value: 'announced',
            label: 'Umumkan',
            color: 'bg-blue-100 text-blue-800 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail: ${period.name}`} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                <div className="flex items-center justify-between">
                    <div className="space-y-2">
                        <Link
                            href="/admin/periods"
                            className="inline-flex items-center gap-2 text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                        >
                            <ArrowLeft className="size-4" />
                            Kembali ke Daftar
                        </Link>
                        <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                            {period.name}
                        </h1>
                        <p className="text-muted-foreground">
                            Semester {period.semester} Tahun {period.year}
                        </p>
                    </div>
                    <div className="flex items-center gap-3">
                        <StatusBadge status={period.status} />
                        <Link
                            href={`/admin/periods/${period.id}/edit`}
                            className="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            <Edit className="size-4" />
                            Edit
                        </Link>
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    <div className="space-y-6 lg:col-span-2">
                        <div className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:bg-gray-900">
                            <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Informasi Periode
                            </h2>
                            <dl className="space-y-4">
                                <div className="grid grid-cols-3 gap-4">
                                    <dt className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Semester
                                    </dt>
                                    <dd className="col-span-2 text-sm text-gray-900 dark:text-gray-100">
                                        {period.semester}
                                    </dd>
                                </div>
                                <div className="grid grid-cols-3 gap-4">
                                    <dt className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Tahun
                                    </dt>
                                    <dd className="col-span-2 text-sm text-gray-900 dark:text-gray-100">
                                        {period.year}
                                    </dd>
                                </div>
                                <div className="grid grid-cols-3 gap-4">
                                    <dt className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Tanggal Mulai
                                    </dt>
                                    <dd className="col-span-2 text-sm text-gray-900 dark:text-gray-100">
                                        {period.start_date_formatted ??
                                            period.start_date}
                                    </dd>
                                </div>
                                <div className="grid grid-cols-3 gap-4">
                                    <dt className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Tanggal Selesai
                                    </dt>
                                    <dd className="col-span-2 text-sm text-gray-900 dark:text-gray-100">
                                        {period.end_date_formatted ??
                                            period.end_date}
                                    </dd>
                                </div>
                                {period.notes && (
                                    <div className="grid grid-cols-3 gap-4">
                                        <dt className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                            Catatan
                                        </dt>
                                        <dd className="col-span-2 text-sm text-gray-900 dark:text-gray-100">
                                            {period.notes}
                                        </dd>
                                    </div>
                                )}
                            </dl>
                        </div>

                        {period.votes.length > 0 && (
                            <div className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:bg-gray-900">
                                <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Daftar Voting
                                </h2>
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead>
                                            <tr className="border-b border-gray-200 dark:border-gray-800">
                                                <th className="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                    Pemilih
                                                </th>
                                                <th className="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                    Yang Dinilai
                                                </th>
                                                <th className="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                    Kategori
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                                            {period.votes.map((vote) => (
                                                <tr
                                                    key={vote.id}
                                                    className="hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                                >
                                                    <td className="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                        {vote.voter.nama}
                                                        <div className="text-xs text-gray-500 dark:text-gray-400">
                                                            {vote.voter.nip}
                                                        </div>
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                        {vote.employee.nama}
                                                        <div className="text-xs text-gray-500 dark:text-gray-400">
                                                            {vote.employee.nip}
                                                        </div>
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                                        {
                                                            vote.employee
                                                                .category.nama
                                                        }
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}

                        {(period.scores?.length || 0) > 0 && (
                            <div className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:bg-gray-900">
                                <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Hasil Peringkat
                                </h2>
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead>
                                            <tr className="border-b border-gray-200 dark:border-gray-800">
                                                <th className="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                    Peringkat
                                                </th>
                                                <th className="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                    Nama
                                                </th>
                                                <th className="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                    Kategori
                                                </th>
                                                <th className="px-4 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                    Skor Total
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                                            {(period.scores || []).map((score) => (
                                                <tr
                                                    key={score.id}
                                                    className="hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                                >
                                                    <td className="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        #{score.rank}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                        {score.employee.nama}
                                                        <div className="text-xs text-gray-500 dark:text-gray-400">
                                                            {score.employee.nip}
                                                        </div>
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                                        {
                                                            score.employee
                                                                .category.nama
                                                        }
                                                    </td>
                                                    <td className="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {score.total_score.toFixed(
                                                            2,
                                                        )}
                                                    </td>
                                                </tr>
                                            ))}
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
                                    <dt className="flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                                        <Users className="size-4" />
                                        Total Voting
                                    </dt>
                                    <dd className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        {period.votes.length}
                                    </dd>
                                </div>
                                <div className="flex items-center justify-between">
                                    <dt className="flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                                        <Trophy className="size-4" />
                                        Total Peserta
                                    </dt>
                                    <dd className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        {period.scores?.length || 0}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:bg-gray-900">
                            <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Pegawai Belum Menilai
                            </h2>
                            <div className="space-y-4">
                                {pendingVotersByCategory.length === 0 ? (
                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                        Tidak ada data pegawai.
                                    </p>
                                ) : (
                                    pendingVotersByCategory.map((category) => (
                                        <div
                                            key={category.id}
                                            className="rounded-lg border border-gray-200 p-4 dark:border-gray-800"
                                        >
                                            <div className="flex items-center justify-between">
                                                <p className="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    {category.nama}
                                                </p>
                                                <span className="text-xs font-medium text-gray-600 dark:text-gray-400">
                                                    {category.pending_count}{' '}
                                                    belum selesai
                                                </span>
                                            </div>
                                            {category.pending.length === 0 ? (
                                                <p className="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                    Semua pegawai sudah
                                                    menyelesaikan penilaian.
                                                </p>
                                            ) : (
                                                <div className="mt-3 divide-y divide-gray-200 dark:divide-gray-800">
                                                    {category.pending.map(
                                                        (penilai) => (
                                                            <div
                                                                key={penilai.id}
                                                                className="flex items-center justify-between py-2"
                                                            >
                                                                <div>
                                                                    <p className="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {
                                                                            penilai.nama
                                                                        }
                                                                    </p>
                                                                    <p className="text-xs text-gray-600 dark:text-gray-400">
                                                                        NIP:{' '}
                                                                        {penilai.nip ??
                                                                            '-'}
                                                                    </p>
                                                                </div>
                                                                <div className="text-right text-xs text-gray-600 dark:text-gray-400">
                                                                    <div className="font-medium text-gray-900 dark:text-gray-100">
                                                                        {
                                                                            penilai.completed
                                                                        }
                                                                        /
                                                                        {
                                                                            penilai.total
                                                                        }
                                                                    </div>
                                                                    <div>
                                                                        Kurang{' '}
                                                                        {
                                                                            penilai.missing
                                                                        }
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        ),
                                                    )}
                                                </div>
                                            )}
                                        </div>
                                    ))
                                )}
                            </div>
                        </div>

                        <div className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:bg-gray-900">
                            <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Ubah Status
                            </h2>
                            <div className="space-y-2">
                                {statusOptions.map((option) => (
                                    <button
                                        key={option.value}
                                        onClick={() =>
                                            handleStatusChange(option.value)
                                        }
                                        disabled={
                                            updatingStatus ||
                                            period.status === option.value
                                        }
                                        className={`w-full rounded-lg px-4 py-2 text-sm font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-50 ${option.color}`}
                                    >
                                        {updatingStatus &&
                                        period.status !== option.value ? (
                                            <div className="flex items-center justify-center gap-2">
                                                <Loader2 className="size-4 animate-spin" />
                                                Memproses...
                                            </div>
                                        ) : (
                                            option.label
                                        )}
                                    </button>
                                ))}
                            </div>
                        </div>
                    </div>

                    <Dialog
                        open={statusDialogOpen}
                        onOpenChange={(open) => {
                            setStatusDialogOpen(open);
                            if (!open) setPendingStatus(null);
                        }}
                    >
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Konfirmasi Perubahan Status</DialogTitle>
                                <DialogDescription>
                                    Apakah Anda yakin ingin mengubah status periode ini menjadi "{getStatusLabel(pendingStatus)}"?
                                </DialogDescription>
                            </DialogHeader>
                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button variant="secondary" disabled={updatingStatus}>
                                        Batal
                                    </Button>
                                </DialogClose>
                                <Button
                                    onClick={confirmStatusChange}
                                    disabled={updatingStatus}
                                >
                                    {updatingStatus ? (
                                        <>
                                            <Loader2 className="mr-2 size-4 animate-spin" />
                                            Memproses...
                                        </>
                                    ) : (
                                        'Ya, Ubah Status'
                                    )}
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>
        </AppLayout>
    );
}
