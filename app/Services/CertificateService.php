<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Criterion;
use App\Models\DisciplineScore;
use App\Models\Employee;
use App\Models\Period;
use App\Models\Vote;
use App\Models\VoteDetail;
use Dompdf\Dompdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateService
{
    /**
     * Generate certificates for all categories in a period.
     */
    public function generateForPeriod(Period $period): array
    {
        $results = [];

        // 1. Generate Best Employee Certificates (Kategori 1 & 2)
        $mainCategories = Category::whereIn('id', [1, 2])->get();
        foreach ($mainCategories as $category) {
            $bestEmployeeCert = $this->generateForWinner($period, $category);
            if ($bestEmployeeCert) {
                $results[] = $bestEmployeeCert;
            }
        }

        // 2. Generate Discipline Certificate (Kategori 3)
        $disciplineCategory = Category::find(3);
        if ($disciplineCategory) {
            $disciplineCert = $this->generateForDisciplineWinner($period, $disciplineCategory);
            if ($disciplineCert) {
                $results[] = $disciplineCert;
            }
        }

        return $results;
    }

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

        // Calculate average score instead of total
        $averageScore = $this->calculateAverageScore($winner, $period, $category->id);
        $criteriaScores = $this->getCriteriaScores($winner, $period, $category->id);

        $qrCodePath = $this->generateQrCode($certificateId);
        $pdfPath = $this->generatePdfWithBackPage(
            $winner,
            $period,
            $category,
            $certificateId,
            $qrCodePath,
            $averageScore,
            'best_employee',
            $criteriaScores
        );

        return [
            'certificate_id' => $certificateId,
            'type' => 'best_employee',
            'employee_id' => $winner->id,
            'period_id' => $period->id,
            'category_id' => $category->id,
            'rank' => $rank,
            'score' => $averageScore,
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
        $winnerData = Vote::query()
            ->select('employee_id', DB::raw('sum(total_score) as total'))
            ->where('period_id', $period->id)
            ->where('category_id', $category->id) // Filter kategori Pejabat/Non-Pejabat
            ->groupBy('employee_id')
            ->orderByDesc('total')
            ->first();

        if (! $winnerData) {
            return null;
        }

        return Employee::find($winnerData->employee_id);
    }

    /**
     * Calculate the total score for a winner employee.
     */
    protected function calculateWinnerScore(Employee $employee, Period $period, int $categoryId): float
    {
        // Paksa hanya menjumlahkan vote dari kategori yang relevan
        return (float) Vote::query()
            ->where('period_id', $period->id)
            ->where('employee_id', $employee->id)
            ->where('category_id', $categoryId)
            ->sum('total_score');
    }

    /**
     * Calculate the average score for a winner employee.
     * Returns the average score per criteria (total average / number of criteria).
     */
    protected function calculateAverageScore(Employee $employee, Period $period, int $categoryId): float
    {
        $votes = Vote::query()
            ->where('period_id', $period->id)
            ->where('employee_id', $employee->id)
            ->where('category_id', $categoryId)
            ->get();

        if ($votes->isEmpty()) {
            return 0.0;
        }

        $totalScore = $votes->sum('total_score');
        $voterCount = $votes->count();

        // First calculate average across all voters
        $averageAcrossVoters = $totalScore / $voterCount;

        // Count criteria for this category
        $criteriaCount = Criterion::where('category_id', $categoryId)->count();

        if ($criteriaCount === 0) {
            return round($averageAcrossVoters, 2);
        }

        // Divide by criteria count to get average per criteria
        return round($averageAcrossVoters / $criteriaCount, 2);
    }

    /**
     * Get criteria scores breakdown for a winner.
     *
     * @return array<int, array{nama: string, average: float}>
     */
    protected function getCriteriaScores(Employee $employee, Period $period, int $categoryId): array
    {
        // Get all criteria for this category
        $criteria = Criterion::where('category_id', $categoryId)
            ->orderBy('urutan')
            ->get();

        // Get all votes for this employee in this period and category
        $voteIds = Vote::query()
            ->where('period_id', $period->id)
            ->where('employee_id', $employee->id)
            ->where('category_id', $categoryId)
            ->pluck('id');

        $result = [];

        foreach ($criteria as $criterion) {
            $details = VoteDetail::whereIn('vote_id', $voteIds)
                ->where('criterion_id', $criterion->id)
                ->get();

            $average = $details->isNotEmpty()
                ? round($details->avg('score'), 2)
                : 0.0;

            $result[] = [
                'nama' => $criterion->nama,
                'average' => $average,
            ];
        }

        return $result;
    }

    /**
     * Generate certificate for a discipline winner.
     */
    public function generateForDisciplineWinner(Period $period, Category $category, int $rank = 1): ?array
    {
        // Cari pemenang dari kategori 3 (Pegawai Disiplin)
        $winnerData = Vote::query()
            ->select('employee_id', DB::raw('sum(total_score) as total'))
            ->where('period_id', $period->id)
            ->where('category_id', 3)
            ->groupBy('employee_id')
            ->orderByDesc('total')
            ->first();

        if (! $winnerData) {
            return null;
        }

        $winner = Employee::find($winnerData->employee_id);

        // Get discipline score data for breakdown
        $disciplineData = $this->getDisciplineScoreData($winner, $period);
        $finalScore = $disciplineData['final_score'] ?? (float) $winnerData->total;

        $certificateId = $this->generateCertificateId($period, $category, $winner, 'DIS');

        $qrCodePath = $this->generateQrCode($certificateId);
        $pdfPath = $this->generatePdfWithBackPage(
            $winner,
            $period,
            $category,
            $certificateId,
            $qrCodePath,
            $finalScore,
            'discipline',
            [],
            $disciplineData
        );

        return [
            'certificate_id' => $certificateId,
            'type' => 'discipline',
            'employee_id' => $winner->id,
            'period_id' => $period->id,
            'category_id' => $category->id,
            'rank' => $rank,
            'score' => $finalScore,
            'qr_code_path' => $qrCodePath,
            'pdf_path' => $pdfPath,
            'issued_at' => now(),
        ];
    }

    /**
     * Get discipline score data for an employee.
     *
     * @return array<string, mixed>
     */
    protected function getDisciplineScoreData(Employee $employee, Period $period): array
    {
        // First try to find by period_id
        $disciplineScore = DisciplineScore::where('employee_id', $employee->id)
            ->where('period_id', $period->id)
            ->first();

        // If not found, try to find by year (fallback for old data without period_id)
        if (! $disciplineScore) {
            $disciplineScore = DisciplineScore::where('employee_id', $employee->id)
                ->where('year', $period->year)
                ->orderByDesc('final_score')
                ->first();
        }

        if (! $disciplineScore) {
            return [
                'score_1' => 0,
                'score_2' => 0,
                'score_3' => 0,
                'final_score' => 0,
                'total_work_days' => 0,
                'present_on_time' => 0,
                'leave_on_time' => 0,
                'late_minutes' => 0,
                'early_leave_minutes' => 0,
            ];
        }

        return [
            'score_1' => (float) $disciplineScore->score_1,
            'score_2' => (float) $disciplineScore->score_2,
            'score_3' => (float) $disciplineScore->score_3,
            'final_score' => (float) $disciplineScore->final_score,
            'total_work_days' => (int) $disciplineScore->total_work_days,
            'present_on_time' => (int) $disciplineScore->present_on_time,
            'leave_on_time' => (int) $disciplineScore->leave_on_time,
            'late_minutes' => (int) $disciplineScore->late_minutes,
            'early_leave_minutes' => (int) $disciplineScore->early_leave_minutes,
        ];
    }

    /**
     * Generate a unique certificate ID.
     */
    protected function generateCertificateId(Period $period, Category $category, Employee $employee, string $prefix = 'CERT'): string
    {
        return $prefix.'-'.$period->id.'-'.$category->id.'-'.$employee->id.'-'.strtoupper(Str::random(8));
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
        float $score,
        string $type = 'best_employee'
    ): string {
        return $this->generatePdfWithBackPage($employee, $period, $category, $certificateId, $qrCodePath, $score, $type);
    }

    /**
     * Generate PDF certificate with back page from template.
     *
     * @param  array<int, array{nama: string, average: float}>  $criteriaScores
     * @param  array<string, mixed>  $disciplineData
     */
    public function generatePdfWithBackPage(
        Employee $employee,
        Period $period,
        Category $category,
        string $certificateId,
        string $qrCodePath,
        float $score,
        string $type = 'best_employee',
        array $criteriaScores = [],
        array $disciplineData = []
    ): string {
        $qrCodeDataUrl = 'data:image/png;base64,'.base64_encode(Storage::get($qrCodePath));

        $backgroundPath = base_path('docs/background-cert-3.png');
        $backgroundDataUrl = '';
        if (File::exists($backgroundPath)) {
            $typeExt = pathinfo($backgroundPath, PATHINFO_EXTENSION);
            $data = File::get($backgroundPath);
            $backgroundDataUrl = 'data:image/'.$typeExt.';base64,'.base64_encode($data);
        }

        $logoPath = base_path('docs/logo-pa-penajam.png');
        $logoDataUrl = '';
        if (File::exists($logoPath)) {
            $typeExt = pathinfo($logoPath, PATHINFO_EXTENSION);
            $data = File::get($logoPath);
            $logoDataUrl = 'data:image/'.$typeExt.';base64,'.base64_encode($data);
        }

        $organizationContext = $this->getOrganizationContext();

        // Generate front page
        $frontViewName = $type === 'discipline' ? 'certificates.discipline' : 'certificates.template';
        $frontHtml = view($frontViewName, [
            'employee' => $employee,
            'period' => $period,
            'category' => $category,
            'certificateId' => $certificateId,
            'qrCodeDataUrl' => $qrCodeDataUrl,
            'backgroundDataUrl' => $backgroundDataUrl,
            'logoDataUrl' => $logoDataUrl,
            'score' => $score,
            'issuedDate' => now()->translatedFormat('d F Y'),
            ...$organizationContext,
        ])->render();

        // Generate back page content (partial without html/body wrapper)
        $backPartialName = $type === 'discipline'
            ? 'certificates.partials.discipline-back-content'
            : 'certificates.partials.template-back-content';
        $backContent = view($backPartialName, [
            'employee' => $employee,
            'period' => $period,
            'category' => $category,
            'criteriaScores' => $criteriaScores,
            'averageScore' => $score,
            'disciplineData' => $disciplineData,
            ...$organizationContext,
        ])->render();

        // Insert back page content before </body> tag
        $combinedHtml = str_replace('</body>', $backContent.'</body>', $frontHtml);

        $dompdf = new Dompdf;
        $dompdf->loadHtml($combinedHtml);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $fileName = 'public/certificates/'.$certificateId.'.pdf';
        Storage::makeDirectory('public/certificates');
        Storage::put($fileName, $dompdf->output());

        return $fileName;
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
