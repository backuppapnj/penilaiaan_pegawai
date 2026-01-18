<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Period;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    public function __construct(
        private CertificateService $certificateService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user->employee_id) {
            return back()->with('error', 'Anda belum terhubung dengan data pegawai.');
        }

        $certificates = Certificate::with(['period', 'category'])
            ->where('employee_id', $user->employee_id)
            ->orderBy('issued_at', 'desc')
            ->get()
            ->map(fn ($cert) => [
                'id' => $cert->id,
                'certificate_id' => $cert->certificate_id,
                'period' => [
                    'id' => $cert->period->id,
                    'name' => $cert->period->name,
                ],
                'category' => [
                    'id' => $cert->category->id,
                    'nama' => $cert->category->nama,
                ],
                'rank' => $cert->rank,
                'score' => $cert->score,
                'issued_at' => $cert->issued_at,
                'download_url' => route('peserta.certificates.download', $cert),
                'verification_url' => $cert->verification_url,
            ]);

        return inertia('Peserta/Certificates/View', [
            'certificates' => $certificates,
        ]);
    }

    public function adminIndex(Request $request): Response
    {
        $certificates = Certificate::query()
            ->with(['employee', 'period', 'category'])
            ->orderByDesc('issued_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($cert) => [
                'id' => $cert->id,
                'certificate_id' => $cert->certificate_id,
                'employee' => [
                    'id' => $cert->employee->id,
                    'nama' => $cert->employee->nama,
                    'nip' => $cert->employee->nip,
                ],
                'period' => [
                    'id' => $cert->period->id,
                    'name' => $cert->period->name,
                ],
                'category' => [
                    'id' => $cert->category->id,
                    'nama' => $cert->category->nama,
                ],
                'rank' => $cert->rank,
                'score' => $cert->score,
                'issued_at' => $cert->issued_at,
                'download_url' => route('peserta.certificates.download', $cert),
                'verification_url' => $cert->verification_url,
            ]);

        return Inertia::render('Admin/Certificates/Index', [
            'certificates' => $certificates,
        ]);
    }

    public function download(Certificate $certificate)
    {
        $user = auth()->user();

        if ($user->employee_id != $certificate->employee_id && ! $user->hasRole('Admin', 'SuperAdmin')) {
            abort(403, 'Anda tidak memiliki akses ke sertifikat ini.');
        }

        if (! Storage::exists($certificate->pdf_path)) {
            return back()->with('error', 'File sertifikat tidak ditemukan.');
        }

        return Storage::download($certificate->pdf_path, "sertifikat-{$certificate->certificate_id}.pdf");
    }

    public function verify(string $certificateId)
    {
        $certificate = Certificate::with(['employee', 'period', 'category'])
            ->where('certificate_id', $certificateId)
            ->firstOrFail();

        return inertia('Public/CertificateVerify', [
            'certificate' => $certificate,
            'isValid' => true,
        ]);
    }

    public function generateForPeriod(Period $period)
    {
        $certificateData = $this->certificateService->generateForPeriod($period);

        foreach ($certificateData as $data) {
            Certificate::updateOrCreate(
                [
                    'period_id' => $data['period_id'],
                    'category_id' => $data['category_id'],
                    'employee_id' => $data['employee_id'],
                ],
                $data
            );
        }

        return back()->with('success', 'Sertifikat berhasil dibuat untuk semua pemenang.');
    }
}
