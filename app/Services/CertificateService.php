<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Period;
use Dompdf\Dompdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateService
{
    /**
     * Generate certificate for a winner in a specific period and category.
     */
    public function generateForWinner(Period $period, Category $category, int $rank = 1): ?array
    {
        $winner = $this->getWinner($period, $category);

        if (! $winner) {
            return null;
        }

        $certificateId = $this->generateCertificateId($period, $category, $winner);
        $totalScore = $this->calculateWinnerScore($winner, $period);

        $qrCodePath = $this->generateQrCode($certificateId);
        $pdfPath = $this->generatePdf($winner, $period, $category, $certificateId, $qrCodePath, $totalScore);

        return [
            'certificate_id' => $certificateId,
            'employee_id' => $winner->id,
            'period_id' => $period->id,
            'category_id' => $category->id,
            'rank' => $rank,
            'score' => $totalScore,
            'qr_code_path' => $qrCodePath,
            'pdf_path' => $pdfPath,
            'issued_at' => now(),
        ];
    }

    /**
     * Get the winner employee for a specific period and category.
     */
    protected function getWinner(Period $period, Category $category): ?Employee
    {
        return Employee::where('category_id', $category->id)
            ->with(['votesReceived' => function ($query) use ($period) {
                $query->where('period_id', $period->id);
            }])
            ->get()
            ->sortByDesc(function ($employee) {
                return $employee->votesReceived->sum('total_score');
            })
            ->first();
    }

    /**
     * Calculate the total score for a winner employee.
     */
    protected function calculateWinnerScore(Employee $employee, Period $period): float
    {
        return (float) $employee->votesReceived->sum('total_score');
    }

    /**
     * Generate a unique certificate ID.
     */
    protected function generateCertificateId(Period $period, Category $category, Employee $employee): string
    {
        return 'CERT-'.$period->id.'-'.$category->id.'-'.$employee->id.'-'.strtoupper(Str::random(8));
    }

    /**
     * Generate QR code for certificate verification.
     */
    public function generateQrCode(string $certificateId): string
    {
        $verifyUrl = url("/verify/{$certificateId}");

        $builder = new Builder(
            data: $verifyUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        $result = $builder->build();

        $fileName = 'public/qr-codes/'.$certificateId.'.png';
        Storage::makeDirectory('public/qr-codes');
        Storage::put($fileName, $result->getString());

        return $fileName;
    }

    /**
     * Generate PDF certificate from template.
     */
    public function generatePdf(
        Employee $employee,
        Period $period,
        Category $category,
        string $certificateId,
        string $qrCodePath,
        float $score
    ): string {
        $qrCodeDataUrl = 'data:image/png;base64,'.base64_encode(Storage::get($qrCodePath));
        
        $backgroundPath = base_path('docs/background-cert.jpg');
        $backgroundDataUrl = '';
        if (File::exists($backgroundPath)) {
            $type = pathinfo($backgroundPath, PATHINFO_EXTENSION);
            $data = File::get($backgroundPath);
            $backgroundDataUrl = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $logoPath = base_path('docs/logo-pa-penajam.png');
        $logoDataUrl = '';
        if (File::exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $data = File::get($logoPath);
            $logoDataUrl = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $html = view('certificates.template', [
            'employee' => $employee,
            'period' => $period,
            'category' => $category,
            'certificateId' => $certificateId,
            'qrCodeDataUrl' => $qrCodeDataUrl,
            'backgroundDataUrl' => $backgroundDataUrl,
            'logoDataUrl' => $logoDataUrl,
            'score' => $score,
            'issuedDate' => now()->translatedFormat('d F Y'),
            ...$this->getOrganizationContext(),
        ])->render();

        $dompdf = new Dompdf;
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $fileName = 'public/certificates/'.$certificateId.'.pdf';
        Storage::makeDirectory('public/certificates');
        Storage::put($fileName, $dompdf->output());

        return $fileName;
    }

    /**
     * Generate certificates for all categories in a period.
     */
    public function generateForPeriod(Period $period): array
    {
        $categories = Category::all();
        $results = [];

        foreach ($categories as $category) {
            $certificateData = $this->generateForWinner($period, $category);

            if ($certificateData) {
                $results[] = $certificateData;
            }
        }

        return $results;
    }

    /**
     * @return array{
     *     institution_name: string,
     *     chairman_name: string,
     *     chairman_nip: string,
     *     chairman_role: string
     * }
     */
    private function getOrganizationContext(): array
    {
        $defaults = [
            'institution_name' => 'Pengadilan Agama Penajam',
            'chairman_name' => "Dr. H. Muhammad Syafi'i, S.H.I., M.H.I.",
            'chairman_nip' => '19700512 199503 1 002',
            'chairman_role' => 'Ketua',
        ];

        $path = base_path('docs/org_structure.json');
        if (! File::exists($path)) {
            return $defaults;
        }

        $org = json_decode(File::get($path), true);
        if (! is_array($org)) {
            return $defaults;
        }

        $chairman = collect($org['pimpinan'] ?? [])
            ->first(fn ($leader) => ($leader['role'] ?? '') === 'Ketua');

        return [
            'institution_name' => $org['instansi'] ?? $defaults['institution_name'],
            'chairman_name' => $chairman['nama'] ?? $defaults['chairman_name'],
            'chairman_nip' => $chairman['nip'] ?? $defaults['chairman_nip'],
            'chairman_role' => $chairman['role'] ?? $defaults['chairman_role'],
        ];
    }
}
