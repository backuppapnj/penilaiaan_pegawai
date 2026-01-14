import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { AlertCircle, ArrowLeft, CheckCircle2, Loader2 } from 'lucide-react';
import { type FormEvent, useState } from 'react';

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

interface PageProps {
    period: {
        id: number;
        name: string;
        start_date: string;
        end_date: string;
    };
    category: Category;
    criteria: Criterion[];
    employees: Employee[];
}

export default function VotingShow({
    period,
    category,
    criteria,
    employees,
}: PageProps) {
    const [selectedEmployee, setSelectedEmployee] = useState<number | null>(
        null,
    );
    const [scores, setScores] = useState<Record<number, number>>({});
    const [errors, setErrors] = useState<Record<string, string>>({});

    const { setData, post, processing, recentlySuccessful } = useForm({
        period_id: period.id,
        employee_id: null as number | null,
        category_id: category.id,
        scores: [] as Array<{ criterion_id: number; score: number }>,
    });

    const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        if (!selectedEmployee) {
            setErrors({
                employee_id: 'Silakan pilih pegawai yang akan dinilai',
            });
            return;
        }

        const missingScores = criteria.filter(
            (criterion) => !scores[criterion.id],
        );
        if (missingScores.length > 0) {
            setErrors({
                scores: `Mohon lengkapi semua nilai. Kurang ${missingScores.length} kriteria belum dinilai.`,
            });
            return;
        }

        const scoresData = criteria.map((criterion) => ({
            criterion_id: criterion.id,
            score: scores[criterion.id] || 0,
        }));

        setData({
            period_id: period.id,
            employee_id: selectedEmployee,
            category_id: category.id,
            scores: scoresData,
        });

        post('/penilai/voting', {
            onSuccess: () => {
                setSelectedEmployee(null);
                setScores({});
                setErrors({});
            },
        });
    };

    const handleScoreChange = (criterionId: number, value: string) => {
        const score = parseInt(value) || 0;
        setScores((prev) => ({
            ...prev,
            [criterionId]: Math.min(100, Math.max(1, score)),
        }));
        if (errors.scores) {
            setErrors((prev) => {
                const newErrors = { ...prev };
                delete newErrors.scores;
                return newErrors;
            });
        }
    };

    const totalScore = Object.values(scores).reduce(
        (sum, score) => sum + score,
        0,
    );
    const averageScore = criteria.length > 0 ? totalScore / criteria.length : 0;
    const allCriteriaRated = criteria.every((criterion) => scores[criterion.id]);

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

                {employees.length === 0 ? (
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
                                <div className="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-900">
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
                                                        max="100"
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
                                                        className="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                                        placeholder="Masukkan nilai (1-100)"
                                                        aria-label={`Nilai untuk ${criterion.nama}`}
                                                        aria-describedby={`score-help-${criterion.id}`}
                                                        disabled={processing}
                                                    />
                                                    <div className="flex items-center gap-2">
                                                        <input
                                                            type="range"
                                                            min="1"
                                                            max="100"
                                                            value={
                                                                scores[
                                                                    criterion.id
                                                                ] || 50
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
                                                    Berikan nilai antara 1-100
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
                                                    Object.values(
                                                        scores,
                                                    ).filter(Boolean).length
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
