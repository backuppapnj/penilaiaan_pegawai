import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Award, Download } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Sertifikat', href: '/admin/certificates' },
];

interface CertificateRow {
    id: number;
    certificate_id: string;
    type?: string;
    employee: {
        id: number;
        nama: string;
        nip: string;
    };
    period: {
        id: number;
        name: string;
    };
    category: {
        id: number;
        nama: string;
    };
    rank: string | null;
    score: number | null;
    issued_at: string | null;
    download_url: string;
    verification_url: string;
}

interface PaginationLink {
    label: string;
    url: string | null;
    active: boolean;
}

interface PaginatedData {
    current_page: number;
    data: CertificateRow[];
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
    certificates: PaginatedData;
}

export default function CertificatesIndex({ certificates }: PageProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sertifikat Pemenang" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                <div className="flex items-start justify-between gap-4">
                    <div className="space-y-2">
                        <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                            Sertifikat Pemenang
                        </h1>
                        <p className="text-muted-foreground">
                            Download sertifikat pemenang yang telah diterbitkan.
                        </p>
                    </div>
                    <div className="flex items-center gap-2 rounded-lg border border-sidebar-border/70 bg-white px-4 py-2 text-sm text-gray-700 dark:border-sidebar-border dark:bg-gray-900 dark:text-gray-200">
                        <Award className="size-4" />
                        Total: {certificates.total}
                    </div>
                </div>

                {certificates.data.length === 0 ? (
                    <div className="rounded-xl border border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-gray-900">
                        <Award className="mx-auto mb-4 size-12 text-gray-400" />
                        <h2 className="mb-2 text-xl font-semibold text-gray-900 dark:text-gray-100">
                            Belum Ada Sertifikat
                        </h2>
                        <p className="text-gray-600 dark:text-gray-400">
                            Sertifikat akan muncul setelah admin melakukan
                            generate.
                        </p>
                    </div>
                ) : (
                    <div className="rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b border-gray-200 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:border-gray-800 dark:text-gray-400">
                                        <th className="px-4 py-3">
                                            Sertifikat
                                        </th>
                                        <th className="px-4 py-3">Jenis</th>
                                        <th className="px-4 py-3">Pegawai</th>
                                        <th className="px-4 py-3">Periode</th>
                                        <th className="px-4 py-3">Kategori</th>
                                        <th className="px-4 py-3">
                                            Peringkat
                                        </th>
                                        <th className="px-4 py-3 text-right">
                                            Skor
                                        </th>
                                        <th className="px-4 py-3">
                                            Diterbitkan
                                        </th>
                                        <th className="px-4 py-3 text-right">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                                    {certificates.data.map((cert) => (
                                        <tr
                                            key={cert.id}
                                            className="hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                        >
                                            <td className="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {cert.certificate_id}
                                            </td>
                                            <td className="px-4 py-3">
                                                <span
                                                    className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
                                                        cert.type === 'discipline'
                                                            ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300'
                                                            : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
                                                    }`}
                                                >
                                                    {cert.type === 'discipline'
                                                        ? 'Disiplin'
                                                        : 'Terbaik'}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                {cert.employee.nama}
                                                <div className="text-xs text-gray-500 dark:text-gray-400">
                                                    {cert.employee.nip}
                                                </div>
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                                {cert.period.name}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                                {cert.category.nama}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                                {cert.rank ?? '-'}
                                            </td>
                                            <td className="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {cert.score ?? '-'}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                                {cert.issued_at
                                                    ? new Date(
                                                          cert.issued_at,
                                                      ).toLocaleDateString(
                                                          'id-ID',
                                                          {
                                                              year: 'numeric',
                                                              month: 'long',
                                                              day: 'numeric',
                                                          },
                                                      )
                                                    : '-'}
                                            </td>
                                            <td className="px-4 py-3 text-right">
                                                <a
                                                    href={cert.download_url}
                                                    className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-xs font-medium text-white transition-colors hover:bg-blue-700"
                                                >
                                                    <Download className="size-4" />
                                                    Download
                                                </a>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {certificates.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                                <div className="text-sm text-gray-700 dark:text-gray-300">
                                    Menampilkan {certificates.from} -{' '}
                                    {certificates.to} dari{' '}
                                    {certificates.total} sertifikat
                                </div>
                                <div className="flex items-center gap-2">
                                    <Link
                                        href={
                                            certificates.prev_page_url || '#'
                                        }
                                        className={`rounded-lg px-3 py-2 text-sm transition-colors ${
                                            !certificates.prev_page_url
                                                ? 'cursor-not-allowed opacity-50'
                                                : 'hover:bg-gray-100 dark:hover:bg-gray-800'
                                        }`}
                                    >
                                        Sebelumnya
                                    </Link>
                                    <span className="text-sm text-gray-700 dark:text-gray-300">
                                        Halaman {certificates.current_page} dari{' '}
                                        {certificates.last_page}
                                    </span>
                                    <Link
                                        href={
                                            certificates.next_page_url || '#'
                                        }
                                        className={`rounded-lg px-3 py-2 text-sm transition-colors ${
                                            !certificates.next_page_url
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
                )}
            </div>
        </AppLayout>
    );
}
