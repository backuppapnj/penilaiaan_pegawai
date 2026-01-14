import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    CheckCircle,
    Clock,
    FileSpreadsheet,
    Trash2,
    Upload,
    XCircle,
} from 'lucide-react';
import { FormEvent, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Import SIKEP', href: '/admin/sikep' },
];

interface Period {
    id: number;
    name: string;
    semester: number;
    year: number;
    status: string;
}

interface RecentImport {
    id: number;
    employee: {
        nama: string;
        nip: string;
        jabatan: string;
    };
    period: {
        name: string;
        year: number;
    };
    final_score: number;
    rank: number;
    created_at: string;
}

interface PageProps {
    periods: Period[];
    recentImports: RecentImport[];
}

export default function SikepImportIndex({
    periods,
    recentImports,
}: PageProps) {
    const [selectedPeriod, setSelectedPeriod] = useState('');
    const [file, setFile] = useState<File | null>(null);
    const [uploading, setUploading] = useState(false);
    const [uploadResult, setUploadResult] = useState<{
        success: boolean;
        message: string;
        data?: { success: number; failed: number; errors: string[] };
    } | null>(null);

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();

        if (!file || !selectedPeriod) {
            alert('Silakan pilih periode dan file Excel');
            return;
        }

        setUploading(true);
        setUploadResult(null);

        const formData = new FormData();
        formData.append('excel_file', file);
        formData.append('period_id', selectedPeriod);

        try {
            const response = await fetch('/admin/sikep', {
                method: 'POST',
                headers: {
                    'X-Inertia': 'true',
                    'X-Inertia-Version': '2',
                    Accept: 'application/json',
                },
                body: formData,
            });

            const result = await response.json();
            setUploadResult(result);

            if (result.success) {
                setFile(null);
                setSelectedPeriod('');
                // Reload the page after successful upload
                setTimeout(() => {
                    router.visit('/admin/sikep');
                }, 2000);
            }
        } catch {
            setUploadResult({
                success: false,
                message: 'Terjadi kesalahan saat mengupload file',
            });
        } finally {
            setUploading(false);
        }
    };

    const handleDelete = async (id: number) => {
        if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            return;
        }

        try {
            const response = await fetch(`/admin/sikep/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-Inertia': 'true',
                    Accept: 'application/json',
                },
            });

            if (response.ok) {
                router.visit('/admin/sikep');
            }
        } catch {
            alert('Terjadi kesalahan saat menghapus data');
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Import Data SIKEP" />

            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
                <div className="space-y-2">
                    <Link
                        href="/admin"
                        className="inline-flex items-center gap-2 text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        <ArrowLeft className="size-4" />
                        Kembali ke Dashboard
                    </Link>
                    <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                        Import Data SIKEP
                    </h1>
                    <p className="text-muted-foreground">
                        Upload file Excel kehadiran pegawai dari sistem SIKEP
                    </p>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    <div className="lg:col-span-2">
                        <div className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800/50">
                            <h2 className="mb-4 text-xl font-semibold text-gray-900 dark:text-gray-100">
                                Upload File Excel
                            </h2>

                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div className="space-y-2">
                                    <label
                                        htmlFor="period"
                                        className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Pilih Periode{' '}
                                        <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="period"
                                        value={selectedPeriod}
                                        onChange={(e) =>
                                            setSelectedPeriod(e.target.value)
                                        }
                                        className="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                        required
                                    >
                                        <option value="">
                                            -- Pilih Periode --
                                        </option>
                                        {periods.map((period) => (
                                            <option
                                                key={period.id}
                                                value={period.id}
                                            >
                                                {period.name} - {period.year}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div className="space-y-2">
                                    <label
                                        htmlFor="file"
                                        className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        File Excel{' '}
                                        <span className="text-red-500">*</span>
                                    </label>
                                    <div className="flex items-center gap-4">
                                        <label className="flex flex-1 cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-8 transition-colors hover:border-blue-500 hover:bg-blue-50 dark:border-gray-600 dark:bg-gray-800 dark:hover:border-blue-500 dark:hover:bg-blue-900/20">
                                            <FileSpreadsheet className="mb-2 size-12 text-gray-400" />
                                            <span className="text-sm text-gray-600 dark:text-gray-400">
                                                {file
                                                    ? file.name
                                                    : 'Klik untuk memilih file Excel'}
                                            </span>
                                            <input
                                                id="file"
                                                type="file"
                                                accept=".xlsx,.xls"
                                                onChange={(e) =>
                                                    setFile(
                                                        e.target.files?.[0] ||
                                                            null,
                                                    )
                                                }
                                                className="hidden"
                                                required
                                            />
                                        </label>
                                    </div>
                                    <p className="text-xs text-gray-600 dark:text-gray-400">
                                        Format yang didukung: .xlsx, .xls
                                    </p>
                                </div>

                                {uploadResult && (
                                    <div
                                        className={`rounded-lg p-4 ${
                                            uploadResult.success
                                                ? 'bg-green-50 text-green-800 dark:bg-green-900/30 dark:text-green-200'
                                                : 'bg-red-50 text-red-800 dark:bg-red-900/30 dark:text-red-200'
                                        }`}
                                    >
                                        <div className="flex items-start gap-3">
                                            {uploadResult.success ? (
                                                <CheckCircle className="size-5 flex-shrink-0" />
                                            ) : (
                                                <XCircle className="size-5 flex-shrink-0" />
                                            )}
                                            <div className="flex-1">
                                                <p className="font-medium">
                                                    {uploadResult.message}
                                                </p>
                                                {uploadResult.data && (
                                                    <div className="mt-2 text-sm">
                                                        <p>
                                                            Berhasil:{' '}
                                                            {
                                                                uploadResult
                                                                    .data
                                                                    .success
                                                            }
                                                        </p>
                                                        <p>
                                                            Gagal:{' '}
                                                            {
                                                                uploadResult
                                                                    .data.failed
                                                            }
                                                        </p>
                                                        {uploadResult.data
                                                            .errors.length >
                                                            0 && (
                                                            <details className="mt-2">
                                                                <summary className="cursor-pointer font-medium">
                                                                    Error
                                                                    Details
                                                                </summary>
                                                                <ul className="mt-2 list-inside list-disc space-y-1">
                                                                    {uploadResult.data.errors.map(
                                                                        (
                                                                            error,
                                                                            idx,
                                                                        ) => (
                                                                            <li
                                                                                key={
                                                                                    idx
                                                                                }
                                                                                className="text-xs"
                                                                            >
                                                                                {
                                                                                    error
                                                                                }
                                                                            </li>
                                                                        ),
                                                                    )}
                                                                </ul>
                                                            </details>
                                                        )}
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                )}

                                <div className="flex items-center justify-end gap-4">
                                    <Link
                                        href="/admin"
                                        className="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                                    >
                                        Batal
                                    </Link>
                                    <button
                                        type="submit"
                                        disabled={
                                            uploading ||
                                            !file ||
                                            !selectedPeriod
                                        }
                                        className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <Upload className="size-4" />
                                        {uploading
                                            ? 'Mengupload...'
                                            : 'Upload File'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div className="lg:col-span-1">
                        <div className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800/50">
                            <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Import Terbaru
                            </h2>

                            {recentImports.length === 0 ? (
                                <div className="flex flex-col items-center justify-center py-8">
                                    <Clock className="mb-3 size-12 text-gray-400" />
                                    <p className="text-center text-sm text-gray-600 dark:text-gray-400">
                                        Belum ada import data
                                    </p>
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    {recentImports.map((importItem) => (
                                        <div
                                            key={importItem.id}
                                            className="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/30"
                                        >
                                            <div className="flex items-start justify-between gap-2">
                                                <div className="min-w-0 flex-1">
                                                    <p className="truncate text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {
                                                            importItem.employee
                                                                .nama
                                                        }
                                                    </p>
                                                    <p className="truncate text-xs text-gray-600 dark:text-gray-400">
                                                        {
                                                            importItem.employee
                                                                .nip
                                                        }
                                                    </p>
                                                    <p className="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                                        Peringkat: #
                                                        {importItem.rank}
                                                    </p>
                                                    <p className="text-xs font-medium text-blue-600 dark:text-blue-400">
                                                        Skor:{' '}
                                                        {importItem.final_score}
                                                    </p>
                                                    <p className="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                                        {importItem.created_at}
                                                    </p>
                                                </div>
                                                <button
                                                    onClick={() =>
                                                        handleDelete(
                                                            importItem.id,
                                                        )
                                                    }
                                                    className="flex-shrink-0 rounded p-1.5 text-gray-400 transition-colors hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                                                    aria-label="Hapus data"
                                                >
                                                    <Trash2 className="size-4" />
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
