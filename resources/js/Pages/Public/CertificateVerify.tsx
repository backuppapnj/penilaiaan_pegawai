import { Head } from '@inertiajs/react';
import { useState } from 'react';

interface Certificate {
    certificate_id: string;
    rank: number;
    score: number;
    issued_at: string;
    qr_code_url: string;
    employee?: {
        nama: string;
        nip: string;
        jabatan?: string | null;
    } | null;
    category?: {
        nama: string;
    } | null;
    period?: {
        name: string;
    } | null;
}

interface PageProps {
    certificate: Certificate | null;
    isValid: boolean;
}

export default function CertificateVerify({ certificate, isValid }: PageProps) {
    const [showQr, setShowQr] = useState(false);

    if (!isValid || !certificate) {
        return (
            <>
                <Head title="Sertifikat Tidak Valid" />
                <div className="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12 sm:px-6 lg:px-8">
                    <div className="w-full max-w-md rounded-lg bg-white p-8 text-center shadow-lg">
                        <svg
                            className="mx-auto h-16 w-16 text-red-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <h2 className="mt-4 text-2xl font-bold text-gray-900">
                            Sertifikat Tidak Valid
                        </h2>
                        <p className="mt-2 text-gray-600">
                            Sertifikat yang Anda cari tidak ditemukan atau tidak
                            valid.
                        </p>
                    </div>
                </div>
            </>
        );
    }

    return (
        <>
            <Head
                title={`Verifikasi Sertifikat - ${certificate.certificate_id}`}
            />

            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50 px-4 py-12 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-4xl">
                    <div className="mb-8 text-center">
                        <div className="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                            <svg
                                className="h-8 w-8 text-green-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                        </div>
                        <h1 className="text-3xl font-bold text-gray-900">
                            Sertifikat Valid
                        </h1>
                        <p className="mt-2 text-gray-600">
                            Sertifikat ini diterbitkan secara resmi oleh
                            Pengadilan Agama Penajam
                        </p>
                    </div>

                    <div className="overflow-hidden rounded-lg bg-white shadow-xl">
                        <div className="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4">
                            <h2 className="text-2xl font-bold text-white">
                                Sertifikat Pegawai Terbaik
                            </h2>
                        </div>

                        <div className="p-6">
                            <div className="space-y-6">
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div className="space-y-4">
                                        <div>
                                            <label className="mb-1 block text-sm font-medium text-gray-500">
                                                ID Sertifikat
                                            </label>
                                            <p className="text-lg font-semibold text-gray-900">
                                                {certificate.certificate_id}
                                            </p>
                                        </div>

                                        <div>
                                            <label className="mb-1 block text-sm font-medium text-gray-500">
                                                Nama Pegawai
                                            </label>
                                            <p className="text-lg font-semibold text-gray-900">
                                                {certificate.employee?.nama}
                                            </p>
                                        </div>

                                        <div>
                                            <label className="mb-1 block text-sm font-medium text-gray-500">
                                                NIP
                                            </label>
                                            <p className="text-gray-900">
                                                {certificate.employee?.nip}
                                            </p>
                                        </div>

                                        <div>
                                            <label className="mb-1 block text-sm font-medium text-gray-500">
                                                Jabatan
                                            </label>
                                            <p className="text-gray-900">
                                                {certificate.employee?.jabatan}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="space-y-4">
                                        <div>
                                            <label className="mb-1 block text-sm font-medium text-gray-500">
                                                Kategori
                                            </label>
                                            <p className="text-lg font-semibold text-gray-900">
                                                {certificate.category?.nama}
                                            </p>
                                        </div>

                                        <div>
                                            <label className="mb-1 block text-sm font-medium text-gray-500">
                                                Periode
                                            </label>
                                            <p className="text-gray-900">
                                                {certificate.period?.name}
                                            </p>
                                        </div>

                                        <div>
                                            <label className="mb-1 block text-sm font-medium text-gray-500">
                                                Peringkat & Skor
                                            </label>
                                            <p className="text-gray-900">
                                                Peringkat {certificate.rank} -
                                                Skor:{' '}
                                                <span className="font-semibold text-blue-600">
                                                    {certificate.score}
                                                </span>
                                            </p>
                                        </div>

                                        <div>
                                            <label className="mb-1 block text-sm font-medium text-gray-500">
                                                Tanggal Diterbitkan
                                            </label>
                                            <p className="text-gray-900">
                                                {new Date(
                                                    certificate.issued_at,
                                                ).toLocaleDateString('id-ID', {
                                                    year: 'numeric',
                                                    month: 'long',
                                                    day: 'numeric',
                                                })}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div className="border-t border-gray-200 pt-6">
                                    <button
                                        onClick={() => setShowQr(!showQr)}
                                        className="inline-flex w-full items-center justify-center rounded-md border border-blue-600 bg-white px-6 py-3 text-base font-medium text-blue-600 transition-colors hover:bg-blue-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                                    >
                                        <svg
                                            className="mr-2 h-5 w-5"
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
                                        {showQr ? 'Sembunyikan' : 'Tampilkan'}{' '}
                                        QR Code
                                    </button>

                                    {showQr && (
                                        <div className="mt-4 flex justify-center">
                                            <div className="rounded-lg border-2 border-gray-200 bg-white p-4">
                                                <img
                                                    src={
                                                        certificate.qr_code_url
                                                    }
                                                    alt="QR Code Sertifikat"
                                                    className="h-48 w-48"
                                                />
                                                <p className="mt-2 text-center text-sm text-gray-600">
                                                    Scan untuk verifikasi
                                                </p>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                <div className="rounded-lg border border-green-200 bg-green-50 p-4">
                                    <div className="flex">
                                        <svg
                                            className="mt-0.5 h-5 w-5 text-green-400"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fillRule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clipRule="evenodd"
                                            />
                                        </svg>
                                        <div className="ml-3">
                                            <p className="text-sm text-green-700">
                                                <strong>Status:</strong>{' '}
                                                Sertifikat ini valid dan
                                                diterbitkan secara resmi. Data
                                                pegawai dan prestasi telah
                                                diverifikasi.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="mt-6 text-center text-sm text-gray-500">
                        <p>
                            Sertifikat ini diterbitkan oleh Pengadilan Agama
                            Penajam dalam rangka apresiasi pegawai terbaik.
                        </p>
                        <p className="mt-2">
                            Hubungi admin jika ada pertanyaan mengenai
                            sertifikat ini.
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
