import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { AlertCircle, ArrowLeft, Award, CheckCircle2, Loader2, TrendingUp } from 'lucide-react';
import { type FormEvent, useEffect, useRef, useState } from 'react';
import { type SharedData } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/penilai',
    },
    {
        title: 'Voting',
        href: '/penilai/voting',
    },
];

interface Criterion {
    id: number;
    nama: string;
    bobot: number;
    urutan: number;
}

interface Category {
    id: number;
    nama: string;
    deskripsi: string;
    urutan: number;
}

interface Employee {
    id: number;
    nama: string;
    nip: string;
    jabatan: string;
    category: Category;
}

interface VoteDetail {
    id: number;
    vote_id: number;
    criterion_id: number | string;
    score: number | string | null;
    criterion: Criterion;
}

interface AutomaticVote {
    id: number;
    employee_id: number;
    total_score: number | string | null;
    early_arrival_count?: number | null;
    employee: Employee;
    voteDetails?: VoteDetail[];
    vote_details?: VoteDetail[];
    voter: {
        id: number;
        name: string;
    };
}

interface PageProps {
    period: {
        id: number;
        name: string;
        start_date: string;
        end_date: string;
        start_date_formatted: string;
        end_date_formatted: string;
    };
    category: Category;
    criteria: Criterion[];
    employees: Employee[];
    isAutomaticVoting: boolean;
    automaticVotes: AutomaticVote[] | null;
    isResultsLocked?: boolean;
}

export default function VotingShow({
    period,
    category,
    criteria,
    employees,
    isAutomaticVoting,
    automaticVotes,
    isResultsLocked,
}: PageProps) {
    const { auth } = usePage<SharedData>().props;
    const userRole = auth.user?.role;
    const isAdmin = userRole === 'Admin' || userRole === 'SuperAdmin';
    const [selectedEmployee, setSelectedEmployee] = useState<number | null>(
        null,
    );
    const [scores, setScores] = useState<Record<number, number | ''>>({});
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [processing, setProcessing] = useState(false);
    const [recentlySuccessful, setRecentlySuccessful] = useState(false);
    const [isGenerating, setIsGenerating] = useState(false);
    const criteriaSectionRef = useRef<HTMLDivElement>(null);

    const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        if (!selectedEmployee) {
            setErrors({
                employee_id: 'Silakan pilih pegawai yang akan dinilai',
            });
            return;
        }

        const missingScores = criteria.filter(
            (criterion) => typeof scores[criterion.id] !== 'number',
        );
        if (missingScores.length > 0) {
            setErrors({
                scores: `Mohon lengkapi semua nilai. Kurang ${missingScores.length} kriteria belum dinilai.`,
            });
            return;
        }

        const scoresData = criteria.map((criterion) => {
            const scoreValue = scores[criterion.id];
            return {
                criterion_id: criterion.id,
                score: typeof scoreValue === 'number' ? scoreValue : 0,
            };
        });

        setProcessing(true);
        router.post(
            '/penilai/voting',
            {
                period_id: period.id,
                employee_id: selectedEmployee,
                category_id: category.id,
                scores: scoresData,
            },
            {
                onSuccess: () => {
                    setSelectedEmployee(null);
                    setScores({});
                    setErrors({});
                    setRecentlySuccessful(true);
                    setTimeout(() => setRecentlySuccessful(false), 3000);
                },
                onFinish: () => setProcessing(false),
                onError: (err) => {
                    // Type assertion untuk error object
                    const errorMap = err as Record<string, string>;
                    setErrors(errorMap);
                },
            },
        );
    };

    const handleScoreChange = (criterionId: number, value: string) => {
        if (value.trim() === '') {
            setScores((prev) => ({
                ...prev,
                [criterionId]: '',
            }));
            return;
        }

        const score = Number.parseInt(value, 10);
        if (Number.isNaN(score)) {
            return;
        }

        setScores((prev) => ({
            ...prev,
            [criterionId]: Math.min(99, Math.max(1, score)),
        }));
        if (errors.scores) {
            setErrors((prev) => {
                const newErrors = { ...prev };
                delete newErrors.scores;
                return newErrors;
            });
        }
    };

    useEffect(() => {
        if (!selectedEmployee) {
            return;
        }

        if (typeof window === 'undefined') {
            return;
        }

        if (!window.matchMedia('(max-width: 768px)').matches) {
            return;
        }

        const timeout = window.setTimeout(() => {
            criteriaSectionRef.current?.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        }, 50);

        return () => window.clearTimeout(timeout);
    }, [selectedEmployee]);

    const totalScore = Object.values(scores).reduce(
        (sum, score) => sum + (typeof score === 'number' ? score : 0),
        0,
    );
    const averageScore = criteria.length > 0 ? totalScore / criteria.length : 0;
    const allCriteriaRated = criteria.every(
        (criterion) => typeof scores[criterion.id] === 'number',
    );
    const isDisciplineCategory = category.id === 3;

    const normalizeScore = (value: unknown): number => {
        if (typeof value === 'number') {
            return Number.isFinite(value) ? value : 0;
        }
        const parsed = Number.parseFloat(String(value));
        return Number.isFinite(parsed) ? parsed : 0;
    };

    const formatScore = (value: unknown): string => {
        if (value === null || value === undefined || value === '') {
            return '-';
        }
        const parsed = typeof value === 'number' ? value : Number.parseFloat(String(value));
        return Number.isFinite(parsed) ? parsed.toFixed(2) : '-';
    };

    const handleGenerateAutomaticVotes = (overwrite = false) => {
        setIsGenerating(true);
        router.post(
            `/penilai/voting/${period.id}/${category.id}/generate`,
            { overwrite },
            {
                onFinish: () => setIsGenerating(false),
            },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Nilai ${category.nama}`} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                <div className="flex items-center gap-4">
                    <Link
                        href="/penilai/voting"
                        className="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        <ArrowLeft className="size-4" />
                        Kembali
                    </Link>
                    <div className="flex-1">
                        <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                            Nilai {category.nama}
                        </h1>
                        <p className="text-muted-foreground">
                            Berikan penilaian pada pegawai sesuai kriteria yang
                            telah ditentukan.
                        </p>
                    </div>
                </div>

                {recentlySuccessful && (
                    <div className="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-950">
                        <div className="flex items-center gap-3">
                            <CheckCircle2 className="size-5 text-green-600 dark:text-green-400" />
                            <p className="text-sm font-medium text-green-900 dark:text-green-100">
                                Terima kasih! Nilai Anda berhasil disimpan
                            </p>
                        </div>
                    </div>
                )}

                {/* Automatic Voting Results */}
                {isAutomaticVoting && automaticVotes && automaticVotes.length > 0 ? (
                    <div className="space-y-6">
                        <div className="rounded-xl border border-blue-200 bg-blue-50 p-6 dark:border-blue-900 dark:bg-blue-950">
                            <div className="flex items-start gap-4">
                                <Award className="size-6 text-blue-600 dark:text-blue-400 mt-1" />
                                <div className="flex-1">
                                    <div className="flex flex-wrap items-start justify-between gap-3">
                                        <h2 className="text-lg font-semibold text-blue-900 dark:text-blue-100">
                                            Voting Otomatis Pegawai Disiplin
                                        </h2>
                                        {isAdmin && isDisciplineCategory && (
                                            <button
                                                type="button"
                                                onClick={() => handleGenerateAutomaticVotes(true)}
                                                disabled={isGenerating}
                                                className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-xs font-medium text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                                {isGenerating ? (
                                                    <>
                                                        <Loader2 className="size-3 animate-spin" />
                                                        Menghasilkan...
                                                    </>
                                                ) : (
                                                    'Generate Ulang (Overwrite)'
                                                )}
                                            </button>
                                        )}
                                    </div>
                                    <p className="mt-2 text-sm text-blue-800 dark:text-blue-200">
                                        Penilaian untuk kategori ini dilakukan secara otomatis berdasarkan data kehadiran dari SIKEP.
                                        Berikut adalah hasil voting yang telah di-generate secara otomatis:
                                    </p>
                                    <div className="mt-4 grid gap-4 md:grid-cols-3">
                                        <div className="rounded-lg bg-white p-3 dark:bg-gray-900">
                                            <p className="text-sm text-gray-600 dark:text-gray-400">Total Pegawai Dinilai</p>
                                            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                {automaticVotes.length}
                                            </p>
                                        </div>
                                        <div className="rounded-lg bg-white p-3 dark:bg-gray-900">
                                            <p className="text-sm text-gray-600 dark:text-gray-400">Rata-rata Skor</p>
                                            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                {(automaticVotes.reduce((sum, vote) => sum + normalizeScore(vote.total_score), 0) / automaticVotes.length).toFixed(2)}
                                            </p>
                                        </div>
                                        <div className="rounded-lg bg-white p-3 dark:bg-gray-900">
                                            <p className="text-sm text-gray-600 dark:text-gray-400">Skor Tertinggi</p>
                                            <p className="text-2xl font-bold text-green-600 dark:text-green-400">
                                                {Math.max(...automaticVotes.map((vote) => normalizeScore(vote.total_score))).toFixed(2)}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Automatic Votes Table */}
                        <div className="rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900">
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead className="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                Peringkat
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                Nama Pegawai
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                NIP
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                Datang Awal
                                            </th>
                                            {criteria.map((criterion) => (
                                                <th key={criterion.id} className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                    {criterion.nama}
                                                    <span className="block text-xs font-normal">({criterion.bobot}%)</span>
                                                </th>
                                            ))}
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                                Total Skor
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                                        {automaticVotes.map((vote, index) => (
                                            <tr
                                                key={vote.id}
                                                className={index < 3 ? 'bg-green-50 dark:bg-green-950' : ''}
                                            >
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    {index === 0 && (
                                                        <span className="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-3 py-1 text-sm font-semibold text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                            <TrendingUp className="size-4" />
                                                            #{index + 1}
                                                        </span>
                                                    )}
                                                    {index === 1 && (
                                                        <span className="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                                                            #{index + 1}
                                                        </span>
                                                    )}
                                                    {index === 2 && (
                                                        <span className="inline-flex items-center gap-1 rounded-full bg-orange-100 px-3 py-1 text-sm font-semibold text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                                            #{index + 1}
                                                        </span>
                                                    )}
                                                    {index > 2 && (
                                                        <span className="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                                            #{index + 1}
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <div className="flex items-center gap-2">
                                                        <span className="font-medium text-gray-900 dark:text-gray-100">
                                                            {vote.employee.nama}
                                                        </span>
                                                        {index < 3 && (
                                                            <Award className="size-4 text-yellow-600 dark:text-yellow-400" />
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                                    {vote.employee.nip}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                                    {vote.early_arrival_count ?? 0}
                                                </td>
                                                {criteria.map((criterion) => {
                                                    const voteDetails =
                                                        vote.voteDetails ??
                                                        vote.vote_details ??
                                                        [];
                                                    const detail = voteDetails.find(
                                                        (d) => Number(d.criterion_id) === criterion.id
                                                    );
                                                    return (
                                                        <td
                                                            key={criterion.id}
                                                            className="whitespace-nowrap px-6 py-4 text-sm"
                                                        >
                                                            <span
                                                                className={`inline-flex rounded-lg px-3 py-1 font-medium ${
                                                                    detail && detail.score >= 80
                                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                                        : detail && detail.score >= 60
                                                                            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                                                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                                }`}
                                                            >
                                                                {detail ? formatScore(detail.score) : '-'}
                                                            </span>
                                                        </td>
                                                    );
                                                })}
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <span className="inline-flex rounded-lg bg-blue-100 px-4 py-2 text-lg font-bold text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        {formatScore(vote.total_score)}
                                                    </span>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div className="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800">
                            <p className="text-sm text-gray-700 dark:text-gray-300">
                                <strong>Catatan:</strong> Voting ini di-generate secara otomatis berdasarkan data kehadiran bulanan dari SIKEP.
                                Data di-update setiap hari jam 01:00. Untuk informasi lebih lanjut, hubungi administrator.
                            </p>
                        </div>
                    </div>
                ) : isAutomaticVoting ? (
                    <div className="rounded-xl border border-yellow-200 bg-yellow-50 p-8 text-center dark:border-yellow-900 dark:bg-yellow-950">
                        <AlertCircle className="mx-auto mb-4 size-12 text-yellow-600 dark:text-yellow-400" />
                        <h2 className="mb-2 text-xl font-semibold text-yellow-900 dark:text-yellow-100">
                            {isResultsLocked
                                ? 'Menunggu Pengumuman'
                                : 'Belum Ada Data Voting Otomatis'}
                        </h2>
                        <p className="text-yellow-800 dark:text-yellow-200">
                            {isResultsLocked
                                ? 'Hasil voting otomatis akan ditampilkan setelah periode diumumkan.'
                                : `Voting otomatis untuk Pegawai Disiplin belum di-generate.${
                                      isAdmin
                                          ? ' Silakan generate data di bawah ini.'
                                          : ' Silakan tunggu administrator untuk generate data.'
                                  }`}
                        </p>
                        {isAdmin && isDisciplineCategory && !isResultsLocked && (
                            <div className="mt-6 flex justify-center">
                                <button
                                    type="button"
                                    onClick={() => handleGenerateAutomaticVotes(true)}
                                    disabled={isGenerating}
                                    className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {isGenerating ? (
                                        <>
                                            <Loader2 className="size-4 animate-spin" />
                                            Menghasilkan...
                                        </>
                                    ) : (
                                        'Generate Ulang (Overwrite)'
                                    )}
                                </button>
                            </div>
                        )}
                    </div>
                ) : employees.length === 0 ? (
                    <div className="rounded-xl border border-yellow-200 bg-yellow-50 p-8 text-center dark:border-yellow-900 dark:bg-yellow-950">
                        <AlertCircle className="mx-auto mb-4 size-12 text-yellow-600 dark:text-yellow-400" />
                        <h2 className="mb-2 text-xl font-semibold text-yellow-900 dark:text-yellow-100">
                            Semua Pegawai Telah Dinilai
                        </h2>
                        <p className="text-yellow-800 dark:text-yellow-200">
                            Anda sudah menilai semua pegawai dalam kategori{' '}
                            {category.nama}.
                        </p>
                        <Link
                            href="/penilai/voting"
                            className="mt-4 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                        >
                            Kembali ke Daftar Kategori
                        </Link>
                    </div>
                ) : (
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-900">
                            <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Pilih Pegawai
                            </h2>
                            <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                                {employees.map((employee) => (
                                    <label
                                        key={employee.id}
                                        className={`relative flex cursor-pointer rounded-lg border-2 p-4 transition-all ${
                                            selectedEmployee === employee.id
                                                ? 'border-blue-500 bg-blue-50 dark:bg-blue-950'
                                                : 'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700'
                                        }`}
                                    >
                                        <input
                                            type="radio"
                                            name="employee_id"
                                            value={employee.id}
                                            checked={
                                                selectedEmployee === employee.id
                                            }
                                            onChange={(e) => {
                                                setSelectedEmployee(
                                                    parseInt(e.target.value),
                                                );
                                                if (errors.employee_id) {
                                                    setErrors((prev) => {
                                                        const newErrors = {
                                                            ...prev,
                                                        };
                                                        delete newErrors.employee_id;
                                                        return newErrors;
                                                    });
                                                }
                                            }}
                                            className="sr-only"
                                            aria-label={`Pilih ${employee.nama}`}
                                        />
                                        <div className="flex-1">
                                            <p className="font-medium text-gray-900 dark:text-gray-100">
                                                {employee.nama}
                                            </p>
                                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                                {employee.nip}
                                            </p>
                                            {employee.jabatan && (
                                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                                    {employee.jabatan}
                                                </p>
                                            )}
                                        </div>
                                        {selectedEmployee === employee.id && (
                                            <CheckCircle2 className="size-5 text-blue-600 dark:text-blue-400" />
                                        )}
                                    </label>
                                ))}
                            </div>
                            {errors.employee_id && (
                                <p
                                    className="mt-2 text-sm text-red-600 dark:text-red-400"
                                    role="alert"
                                >
                                    {errors.employee_id}
                                </p>
                            )}
                        </div>

                        {selectedEmployee && (
                            <>
                                <div
                                    ref={criteriaSectionRef}
                                    className="scroll-mt-24 rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-900"
                                >
                                    <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        Kriteria Penilaian
                                    </h2>
                                    <div className="space-y-6">
                                        {criteria.map((criterion, index) => (
                                            <div
                                                key={criterion.id}
                                                className="rounded-lg border border-gray-200 p-4 dark:border-gray-800"
                                            >
                                                <div className="mb-3">
                                                    <label
                                                        htmlFor={`score-${criterion.id}`}
                                                        className="flex items-center gap-2"
                                                    >
                                                        <span className="flex size-6 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-600 dark:bg-blue-900 dark:text-blue-400">
                                                            {index + 1}
                                                        </span>
                                                        <span className="font-medium text-gray-900 dark:text-gray-100">
                                                            {criterion.nama}
                                                        </span>
                                                        <span className="text-sm text-gray-600 dark:text-gray-400">
                                                            (Bobot:{' '}
                                                            {criterion.bobot}%)
                                                        </span>
                                                    </label>
                                                </div>
                                                <div className="flex items-center gap-4">
                                                    <input
                                                        id={`score-${criterion.id}`}
                                                        type="number"
                                                        min="1"
                                                        max="99"
                                                        value={
                                                            scores[
                                                                criterion.id
                                                            ] || ''
                                                        }
                                                        onChange={(e) =>
                                                            handleScoreChange(
                                                                criterion.id,
                                                                e.target.value,
                                                            )
                                                        }
                                                        onFocus={(e) =>
                                                            e.currentTarget.select()
                                                        }
                                                        className="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                                        placeholder="Masukkan nilai (1-99)"
                                                        aria-label={`Nilai untuk ${criterion.nama}`}
                                                        aria-describedby={`score-help-${criterion.id}`}
                                                        disabled={processing}
                                                    />
                                                    <div className="flex items-center gap-2">
                                                        <input
                                                            type="range"
                                                            min="1"
                                                            max="99"
                                                            value={
                                                                typeof scores[
                                                                    criterion.id
                                                                ] === 'number'
                                                                    ? scores[
                                                                          criterion
                                                                              .id
                                                                      ]
                                                                    : 50
                                                            }
                                                            onChange={(e) =>
                                                                handleScoreChange(
                                                                    criterion.id,
                                                                    e.target
                                                                        .value,
                                                                )
                                                            }
                                                            className="w-32 accent-blue-600"
                                                            disabled={
                                                                processing
                                                            }
                                                            aria-label={`Slider nilai untuk ${criterion.nama}`}
                                                        />
                                                        <span className="min-w-[3rem] text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            {scores[
                                                                criterion.id
                                                            ] || '-'}
                                                        </span>
                                                    </div>
                                                </div>
                                                <p
                                                    id={`score-help-${criterion.id}`}
                                                    className="mt-2 text-sm text-gray-600 dark:text-gray-400"
                                                >
                                                    Berikan nilai antara 1-99
                                                    untuk kriteria ini
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                    {errors.scores && (
                                        <p
                                            className="mt-4 text-sm text-red-600 dark:text-red-400"
                                            role="alert"
                                        >
                                            {errors.scores}
                                        </p>
                                    )}
                                </div>

                                <div className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-900">
                                    <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        Ringkasan Penilaian
                                    </h2>
                                    <div className="grid gap-4 md:grid-cols-3">
                                        <div className="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                                Total Nilai
                                            </p>
                                            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                {totalScore}
                                            </p>
                                        </div>
                                        <div className="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                                Rata-rata
                                            </p>
                                            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                {averageScore.toFixed(2)}
                                            </p>
                                        </div>
                                        <div className="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                                Kriteria Dinilai
                                            </p>
                                            <p className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                {
                                                    Object.values(scores).filter(
                                                        (score) =>
                                                            typeof score ===
                                                            'number',
                                                    ).length
                                                }
                                                /{criteria.length}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div className="flex justify-end gap-3">
                                    <Link
                                        href="/penilai/voting"
                                        className="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-6 py-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                    >
                                        Batal
                                    </Link>
                                    <button
                                        type="submit"
                                        disabled={
                                            processing || !allCriteriaRated
                                        }
                                        className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        {processing ? (
                                            <>
                                                <Loader2 className="size-4 animate-spin" />
                                                Menyimpan...
                                            </>
                                        ) : (
                                            'Simpan Penilaian'
                                        )}
                                    </button>
                                </div>
                            </>
                        )}
                    </form>
                )}
            </div>
        </AppLayout>
    );
}
