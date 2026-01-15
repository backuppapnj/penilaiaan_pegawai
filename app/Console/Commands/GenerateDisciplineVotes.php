<?php

namespace App\Console\Commands;

use App\Models\Period;
use App\Services\DisciplineVoteService;
use Illuminate\Console\Command;

class GenerateDisciplineVotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discipline:generate-votes
                            {period : Period ID}
                            {--voter= : User ID sebagai voter (default: admin)}
                            {--overwrite : Overwrite existing votes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate votes dari discipline_scores untuk kategori Pegawai Disiplin';

    /**
     * Execute the console command.
     */
    public function handle(DisciplineVoteService $service): int
    {
        $periodId = (int) $this->argument('period');
        $voterId = $this->option('voter') ? (int) $this->option('voter') : null;
        $overwrite = $this->option('overwrite') ?? false;

        // Validate period
        $period = Period::find($periodId);
        if (! $period) {
            $this->error("Period dengan ID {$periodId} tidak ditemukan!");

            return self::FAILURE;
        }

        $this->info("Generating votes untuk period: {$period->name} (ID: {$periodId})");
        $this->info('Category: Pegawai Disiplin');

        if ($voterId) {
            $this->info("Voter ID: {$voterId}");
        } else {
            $this->info('Voter: (Auto-select admin)');
        }

        if ($overwrite) {
            $this->warn('Mode: OVERWRITE (akan menimpa vote yang sudah ada)');
        }

        $this->newLine();

        // Check if discipline votes already exist
        if ($service->hasDisciplineVotes($periodId)) {
            $this->warn('Vote untuk Pegawai Disiplin sudah ada untuk period ini!');

            if (! $overwrite && ! $this->confirm('Lanjutkan? (akan skip vote yang sudah ada)', true)) {
                return self::SUCCESS;
            }
        }

        // Generate votes
        $result = $service->generateVotes($periodId, $voterId, ['overwrite' => $overwrite]);

        // Display results
        $this->newLine();
        $this->info('=== HASIL ===');
        $this->info("Success: {$result['success']} votes");

        if ($result['failed'] > 0) {
            $this->error("Failed: {$result['failed']} votes");
        }

        if (! empty($result['errors'])) {
            $this->newLine();
            $this->error('Errors:');
            foreach ($result['errors'] as $error) {
                $this->error("  - {$error}");
            }

            return self::FAILURE;
        }

        // Show summary
        $this->newLine();
        $summary = $service->getSummary($periodId);
        $this->info("Total votes: {$summary['total_votes']}");
        $this->info('Average score: '.number_format($summary['average_score'], 2));

        return self::SUCCESS;
    }
}
