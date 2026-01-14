import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { useState } from 'react';

interface Certificate {
    id: number;
    score: number;
    issued_at: string;
    download_url: string;
    verification_url: string;
    period?: {
        name: string;
    } | null;
    category?: {
        nama: string;
    } | null;
}

interface PageProps {
    certificates: Certificate[];
}

export default function CertificateView({ certificates }: PageProps) {
    const [searchQuery, setSearchQuery] = useState('');

    const filteredCertificates = certificates.filter(
        (cert) =>
            cert.period?.name
                ?.toLowerCase()
                .includes(searchQuery.toLowerCase()) ||
            cert.category?.nama
                ?.toLowerCase()
                .includes(searchQuery.toLowerCase()),
    );

    return (
        <AppLayout>
            <Head title="Sertifikat Saya" />

            <div className="min-h-screen bg-gray-50 py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900">
                            Sertifikat Saya
                        </h1>
                        <p className="mt-2 text-gray-600">
                            Daftar sertifikat pegawai terbaik yang telah Anda
                            terima
                        </p>
                    </div>

                    <div className="mb-6">
                        <input
                            type="text"
                            placeholder="Cari sertifikat berdasarkan periode atau kategori..."
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    {filteredCertificates.length === 0 ? (
                        <div className="rounded-lg bg-white p-12 text-center shadow-md">
                            <svg
                                className="mx-auto h-24 w-24 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                            <h3 className="mt-4 text-lg font-medium text-gray-900">
                                {searchQuery
                                    ? 'Tidak ada sertifikat yang cocok'
                                    : 'Belum ada sertifikat'}
                            </h3>
                            <p className="mt-2 text-gray-500">
                                {searchQuery
                                    ? 'Coba kata kunci pencarian lain'
                                    : 'Sertifikat akan muncul di sini setelah Anda dinyatakan sebagai pemenang'}
                            </p>
                        </div>
                    ) : (
                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {filteredCertificates.map((certificate) => (
                                <div
                                    key={certificate.id}
                                    className="overflow-hidden rounded-lg bg-white shadow-md transition-shadow duration-300 hover:shadow-lg"
                                >
                                    <div className="bg-gradient-to-r from-blue-600 to-purple-600 p-6">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center">
                                                <svg
                                                    className="h-12 w-12 text-white"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth={2}
                                                        d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"
                                                    />
                                                </svg>
                                                <div className="ml-4">
                                                    <p className="text-sm font-medium text-white">
                                                        Peringkat 1
                                                    </p>
                                                    <p className="text-xs text-blue-100">
                                                        Pemenang Kategori
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="p-6">
                                        <div className="mb-4">
                                            <h3 className="text-lg font-semibold text-gray-900">
                                                {certificate.category?.nama}
                                            </h3>
                                            <p className="mt-1 text-sm text-gray-600">
                                                {certificate.period?.name}
                                            </p>
                                        </div>

                                        <div className="mb-4 space-y-2">
                                            <div className="flex items-center text-sm text-gray-600">
                                                <svg
                                                    className="mr-2 h-4 w-4 text-gray-400"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth={2}
                                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                                                    />
                                                </svg>
                                                <span>
                                                    Skor: {certificate.score}
                                                </span>
                                            </div>
                                            <div className="flex items-center text-sm text-gray-600">
                                                <svg
                                                    className="mr-2 h-4 w-4 text-gray-400"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth={2}
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                    />
                                                </svg>
                                                <span>
                                                    Diterbit:{' '}
                                                    {new Date(
                                                        certificate.issued_at,
                                                    ).toLocaleDateString(
                                                        'id-ID',
                                                        {
                                                            year: 'numeric',
                                                            month: 'long',
                                                            day: 'numeric',
                                                        },
                                                    )}
                                                </span>
                                            </div>
                                        </div>

                                        <div className="flex gap-2">
                                            <a
                                                href={certificate.download_url}
                                                className="inline-flex flex-1 items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                                            >
                                                <svg
                                                    className="mr-2 h-4 w-4"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth={2}
                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                                    />
                                                </svg>
                                                Unduh PDF
                                            </a>
                                            <a
                                                href={
                                                    certificate.verification_url
                                                }
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                                            >
                                                <svg
                                                    className="mr-2 h-4 w-4"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth={2}
                                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                                                    />
                                                </svg>
                                                Verifikasi
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}

                    <div className="mt-8 rounded-lg border border-blue-200 bg-blue-50 p-4">
                        <div className="flex">
                            <svg
                                className="mt-0.5 h-5 w-5 text-blue-400"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fillRule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clipRule="evenodd"
                                />
                            </svg>
                            <div className="ml-3">
                                <p className="text-sm text-blue-700">
                                    <strong>Info:</strong> Scan QR Code pada
                                    sertifikat untuk memverifikasi keaslian
                                    dokumen. Sertifikat dapat diunduh dalam
                                    format PDF.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
