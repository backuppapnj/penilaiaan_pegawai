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
import { ArrowLeft, Calendar, Edit, Eye, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Periode', href: '/admin/periods' },
];

interface Period {
    id: number;
    name: string;
    semester: number;
    year: number;
    start_date: string;
    end_date: string;
    status: 'draft' | 'open' | 'closed' | 'announced';
    votes_count: number;
}

interface PageProps {
    periods: Period[];
}

export default function PeriodIndex({ periods }: PageProps) {
    const [deletingId, setDeletingId] = useState<number | null>(null);
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [selectedPeriod, setSelectedPeriod] = useState<Period | null>(null);

    const openDeleteDialog = (period: Period) => {
        setSelectedPeriod(period);
        setDeleteDialogOpen(true);
    };

    const handleDeleteDialogChange = (open: boolean) => {
        setDeleteDialogOpen(open);
        if (!open) {
            setSelectedPeriod(null);
        }
    };

    const handleConfirmDelete = () => {
        if (!selectedPeriod) return;

        const id = selectedPeriod.id;
        setDeletingId(id);
        router.delete(`/admin/periods/${id}`, {
            onFinish: () => {
                setDeletingId(null);
                setDeleteDialogOpen(false);
                setSelectedPeriod(null);
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Kelola Periode" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                <div className="flex items-center justify-between">
                    <div className="space-y-2">
                        <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                            Kelola Periode
                        </h1>
                        <p className="text-muted-foreground">
                            Kelola semua periode penilaian dan voting
                        </p>
                    </div>
                    <Link
                        href="/admin/periods/create"
                        className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        <Plus className="size-4" />
                        Buat Periode Baru
                    </Link>
                </div>

                <div className="rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-900">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50">
                                    <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Nama Periode
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Tahun
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Semester
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Tanggal
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Status
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Suara
                                    </th>
                                    <th className="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                                {periods.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={7}
                                            className="px-6 py-12 text-center"
                                        >
                                            <Calendar className="mx-auto mb-3 size-12 text-gray-400" />
                                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                                Belum ada periode yang dibuat
                                            </p>
                                            <Link
                                                href="/admin/periods/create"
                                                className="mt-4 inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
                                            >
                                                <Plus className="size-4" />
                                                Buat periode pertama
                                            </Link>
                                        </td>
                                    </tr>
                                ) : (
                                    periods.map((period) => (
                                        <tr
                                            key={period.id}
                                            className="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                        >
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {period.name}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-600 dark:text-gray-400">
                                                    {period.year}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-600 dark:text-gray-400">
                                                    {period.semester}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-600 dark:text-gray-400">
                                                    {period.start_date} -{' '}
                                                    {period.end_date}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <StatusBadge
                                                    status={period.status}
                                                />
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-600 dark:text-gray-400">
                                                    {period.votes_count} suara
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                                <div className="flex items-center justify-end gap-2">
                                                    <Link
                                                        href={`/admin/periods/${period.id}`}
                                                        className="inline-flex items-center gap-1 rounded p-1.5 text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
                                                        aria-label="Lihat detail"
                                                    >
                                                        <Eye className="size-4" />
                                                    </Link>
                                                    <Link
                                                        href={`/admin/periods/${period.id}/edit`}
                                                        className="inline-flex items-center gap-1 rounded p-1.5 text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
                                                        aria-label="Edit"
                                                    >
                                                        <Edit className="size-4" />
                                                    </Link>
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            openDeleteDialog(
                                                                period,
                                                            )
                                                        }
                                                        disabled={
                                                            deletingId ===
                                                                period.id ||
                                                            period.status ===
                                                                'open'
                                                        }
                                                        className="inline-flex items-center gap-1 rounded p-1.5 text-gray-600 transition-colors hover:bg-red-100 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-50 dark:text-gray-400 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                                                        aria-label="Hapus"
                                                    >
                                                        <Trash2 className="size-4" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div className="flex items-center justify-between">
                    <Link
                        href="/admin"
                        className="inline-flex items-center gap-2 text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        <ArrowLeft className="size-4" />
                        Kembali ke Dashboard
                    </Link>
                    {periods.length > 0 && (
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            Total {periods.length} periode
                        </p>
                    )}
                </div>
                <Dialog
                    open={deleteDialogOpen}
                    onOpenChange={handleDeleteDialogChange}
                >
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Hapus Periode</DialogTitle>
                            <DialogDescription>
                                {selectedPeriod
                                    ? `Anda yakin ingin menghapus periode "${selectedPeriod.name}"? Tindakan ini tidak dapat dibatalkan.`
                                    : 'Anda yakin ingin menghapus periode ini? Tindakan ini tidak dapat dibatalkan.'}
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <DialogClose asChild>
                                <Button
                                    type="button"
                                    variant="secondary"
                                >
                                    Batal
                                </Button>
                            </DialogClose>
                            <Button
                                type="button"
                                variant="destructive"
                                onClick={handleConfirmDelete}
                                disabled={
                                    !selectedPeriod ||
                                    (selectedPeriod &&
                                        deletingId === selectedPeriod.id)
                                }
                            >
                                {selectedPeriod &&
                                deletingId === selectedPeriod.id
                                    ? 'Menghapus...'
                                    : 'Hapus'}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}
