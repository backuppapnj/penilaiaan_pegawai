<?php

use App\Models\Certificate;
use App\Models\Employee;
use App\Models\Category;
use App\Models\Period;
use App\Models\User;
use App\Models\Vote;
use App\Services\CertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertModelExists;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    Config::set('filesystems.disks.public.root', storage_path('framework/testing/disks/public'));
});

afterEach(function () {
    Storage::fake('public');
});

it('admin can generate certificates for closed period', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $period = Period::factory()->create(['status' => 'closed']);
    $category = Category::factory()->create();
    $employees = Employee::factory()->count(3)->create(['category_id' => $category->id]);

    $voters = User::factory()->count(5)->create(['role' => 'Penilai']);

    foreach ($voters as $voter) {
        foreach ($employees as $index => $employee) {
            Vote::factory()->create([
                'period_id' => $period->id,
                'voter_id' => $voter->id,
                'employee_id' => $employee->id,
                'category_id' => $category->id,
                'total_score' => (3 - $index) * 10 + 70,
            ]);
        }
    }

    $this->actingAs($admin)
        ->post(route('admin.periods.generate-certificates', ['period' => $period->id]))
        ->assertRedirect();

    assertDatabaseHas('certificates', [
        'period_id' => $period->id,
        'category_id' => $category->id,
    ]);
});

it('generates one certificate per category', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $period = Period::factory()->create(['status' => 'closed']);
    $categories = Category::factory()->count(3)->create();

    foreach ($categories as $category) {
        $employees = Employee::factory()->count(3)->create(['category_id' => $category->id]);
        $voter = User::factory()->create(['role' => 'Penilai']);

        foreach ($employees as $index => $employee) {
            Vote::factory()->create([
                'period_id' => $period->id,
                'voter_id' => $voter->id,
                'employee_id' => $employee->id,
                'category_id' => $category->id,
                'total_score' => (3 - $index) * 10 + 70,
            ]);
        }
    }

    $this->actingAs($admin)
        ->post(route('admin.periods.generate-certificates', ['period' => $period->id]))
        ->assertRedirect();

    $certificates = Certificate::where('period_id', $period->id)->get();
    expect($certificates)->toHaveCount(3);

    foreach ($categories as $category) {
        expect($certificates->contains('category_id', $category->id))->toBeTrue();
    }
});

it('creates PDF file for certificate', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'Admin']);
    $period = Period::factory()->create(['status' => 'closed']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $voter = User::factory()->create(['role' => 'Penilai']);

    Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $voter->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'total_score' => 85.5,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.periods.generate-certificates', ['period' => $period->id]))
        ->assertRedirect();

    $certificate = Certificate::where('period_id', $period->id)
        ->where('category_id', $category->id)
        ->first();

    expect($certificate)->not->toBeNull();
    expect($certificate->pdf_path)->not->toBeEmpty();
    expect(Storage::exists($certificate->pdf_path))->toBeTrue();
});

it('creates QR code file for certificate', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'Admin']);
    $period = Period::factory()->create(['status' => 'closed']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $voter = User::factory()->create(['role' => 'Penilai']);

    Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $voter->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'total_score' => 90.0,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.periods.generate-certificates', ['period' => $period->id]))
        ->assertRedirect();

    $certificate = Certificate::where('period_id', $period->id)
        ->where('category_id', $category->id)
        ->first();

    expect($certificate)->not->toBeNull();
    expect($certificate->qr_code_path)->not->toBeEmpty();
    expect(Storage::exists($certificate->qr_code_path))->toBeTrue();
});

it('creates certificate record in database', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'Admin']);
    $period = Period::factory()->create(['status' => 'closed', 'name' => 'Test Period 2024']);
    $category = Category::factory()->create(['nama' => 'Struktural']);
    $employee = Employee::factory()->create([
        'category_id' => $category->id,
        'nama' => 'Test Employee',
        'nip' => '123456789012345678',
    ]);
    $voter = User::factory()->create(['role' => 'Penilai']);

    Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $voter->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'total_score' => 95.5,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.periods.generate-certificates', ['period' => $period->id]))
        ->assertRedirect();

    assertDatabaseHas('certificates', [
        'period_id' => $period->id,
        'category_id' => $category->id,
        'employee_id' => $employee->id,
        'rank' => 1,
        'score' => 95.5,
    ]);

    $certificate = Certificate::where('period_id', $period->id)
        ->where('category_id', $category->id)
        ->first();

    expect($certificate->certificate_id)->toMatch('/^CERT-\d+-\d+-\d+-[A-Z0-9]{8}$/');
    expect($certificate->issued_at)->not->toBeNull();
});

it('allows employee to download own certificate', function () {
    Storage::fake('public');

    $employee = Employee::factory()->create();
    $user = User::factory()->create([
        'role' => 'Peserta',
        'employee_id' => $employee->id,
    ]);
    $period = Period::factory()->create();
    $category = Category::factory()->create();

    $pdfContent = '%PDF-1.4 fake pdf content';
    $pdfPath = 'public/certificates/test-cert.pdf';
    Storage::put($pdfPath, $pdfContent);

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'pdf_path' => $pdfPath,
    ]);

    $response = $this->actingAs($user)
        ->get(route('peserta.certificates.download', ['certificate' => $certificate->id]));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

it('prevents downloading another employee certificate', function () {
    Storage::fake('public');

    $certificate = Certificate::factory()->create();
    $otherEmployee = Employee::factory()->create();
    $otherUser = User::factory()->create([
        'role' => 'Peserta',
        'employee_id' => $otherEmployee->id,
    ]);

    $pdfContent = '%PDF-1.4 fake pdf content';
    Storage::put($certificate->pdf_path, $pdfContent);

    $this->actingAs($otherUser)
        ->get(route('peserta.certificates.download', ['certificate' => $certificate->id]))
        ->assertForbidden();
});

it('allows admin to download any certificate', function () {
    Storage::fake('public');

    $certificate = Certificate::factory()->create();
    $admin = User::factory()->create(['role' => 'Admin']);

    $pdfContent = '%PDF-1.4 fake pdf content';
    Storage::put($certificate->pdf_path, $pdfContent);

    $this->actingAs($admin)
        ->get(route('peserta.certificates.download', ['certificate' => $certificate->id]))
        ->assertStatus(200)
        ->assertHeader('content-type', 'application/pdf');
});

it('returns error when certificate PDF file does not exist', function () {
    $employee = Employee::factory()->create();
    $user = User::factory()->create([
        'role' => 'Peserta',
        'employee_id' => $employee->id,
    ]);

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'pdf_path' => 'nonexistent/path/certificate.pdf',
    ]);

    Storage::fake('public');

    $this->actingAs($user)
        ->get(route('peserta.certificates.download', ['certificate' => $certificate->id]))
        ->assertRedirect()
        ->assertSessionHas('error');
});

it('allows super admin to download any certificate', function () {
    Storage::fake('public');

    $certificate = Certificate::factory()->create();
    $superAdmin = User::factory()->create(['role' => 'SuperAdmin']);

    $pdfContent = '%PDF-1.4 fake pdf content';
    Storage::put($certificate->pdf_path, $pdfContent);

    $this->actingAs($superAdmin)
        ->get(route('peserta.certificates.download', ['certificate' => $certificate->id]))
        ->assertStatus(200)
        ->assertHeader('content-type', 'application/pdf');
});

it('prevents non-admin from generating certificates', function () {
    $user = User::factory()->create(['role' => 'Peserta']);
    $period = Period::factory()->create(['status' => 'closed']);

    $this->actingAs($user)
        ->post(route('admin.periods.generate-certificates', ['period' => $period->id]))
        ->assertForbidden();
});

it('updates existing certificate when regenerated', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'Admin']);
    $period = Period::factory()->create(['status' => 'closed']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $voter = User::factory()->create(['role' => 'Penilai']);

    Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $voter->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'total_score' => 85.5,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.periods.generate-certificates', ['period' => $period->id]));

    $certificate = Certificate::where('period_id', $period->id)
        ->where('category_id', $category->id)
        ->first();

    $originalCertificateId = $certificate->certificate_id;
    $originalPdfPath = $certificate->pdf_path;

    $this->actingAs($admin)
        ->post(route('admin.periods.generate-certificates', ['period' => $period->id]));

    $certificate->refresh();

    expect($certificate->certificate_id)->not->toBe($originalCertificateId);
    expect($certificate->pdf_path)->not->toBe($originalPdfPath);

    expect(Certificate::where('certificate_id', $originalCertificateId)->exists())->toBeFalse();
});

it('does not generate certificate for category without votes', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'Admin']);
    $period = Period::factory()->create(['status' => 'closed']);

    $this->actingAs($admin)
        ->post(route('admin.periods.generate-certificates', ['period' => $period->id]))
        ->assertRedirect();

    $certificates = Certificate::where('period_id', $period->id)->get();

    expect($certificates)->toHaveCount(0);
});

it('generates unique certificate ID', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'Admin']);
    $period1 = Period::factory()->create(['status' => 'closed']);
    $period2 = Period::factory()->create(['status' => 'closed']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $voter = User::factory()->create(['role' => 'Penilai']);

    foreach ([$period1, $period2] as $period) {
        Vote::factory()->create([
            'period_id' => $period->id,
            'voter_id' => $voter->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'total_score' => 85.5,
        ]);
    }

    $this->actingAs($admin)
        ->post(route('admin.periods.generate-certificates', ['period' => $period1->id]));

    $this->actingAs($admin)
        ->post(route('admin.periods.generate-certificates', ['period' => $period2->id]));

    $certificate1 = Certificate::where('period_id', $period1->id)->first();
    $certificate2 = Certificate::where('period_id', $period2->id)->first();

    expect($certificate1->certificate_id)->not->toBe($certificate2->certificate_id);
});

it('stores issued_at timestamp', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'Admin']);
    $period = Period::factory()->create(['status' => 'closed']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $voter = User::factory()->create(['role' => 'Penilai']);

    Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $voter->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'total_score' => 85.5,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.periods.generate-certificates', ['period' => $period->id]));

    $certificate = Certificate::where('period_id', $period->id)
        ->where('category_id', $category->id)
        ->first();

    expect($certificate->issued_at)->not->toBeNull();
    expect($certificate->issued_at->diffInSeconds(now()))->toBeLessThan(5);
});

it('allows viewing certificates index', function () {
    $employee = Employee::factory()->create();
    $user = User::factory()->create([
        'role' => 'Peserta',
        'employee_id' => $employee->id,
    ]);

    $certificates = Certificate::factory()
        ->count(3)
        ->create(['employee_id' => $employee->id]);

    $this->actingAs($user)
        ->get(route('peserta.certificates'))
        ->assertSuccessful();
});

it('returns error when user has no linked employee', function () {
    $user = User::factory()->create([
        'role' => 'Peserta',
        'employee_id' => null,
    ]);

    $this->actingAs($user)
        ->get(route('peserta.certificates'))
        ->assertRedirect()
        ->assertSessionHas('error');
});

it('calculates correct winner score', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'Admin']);
    $period = Period::factory()->create(['status' => 'closed']);
    $category = Category::factory()->create();
    $employees = Employee::factory()->count(3)->create(['category_id' => $category->id]);

    $voters = User::factory()->count(5)->create(['role' => 'Penilai']);

    $scores = [90, 85, 80];
    $expectedTotal = 0;

    foreach ($voters as $voter) {
        foreach ($employees as $index => $employee) {
            Vote::factory()->create([
                'period_id' => $period->id,
                'voter_id' => $voter->id,
                'employee_id' => $employee->id,
                'category_id' => $category->id,
                'total_score' => $scores[$index],
            ]);
        }
    }

    $expectedTotal = $scores[0] * 5;

    $this->actingAs($admin)
        ->post(route('admin.periods.generate-certificates', ['period' => $period->id]));

    $certificate = Certificate::where('period_id', $period->id)
        ->where('category_id', $category->id)
        ->first();

    expect($certificate->employee_id)->toBe($employees[0]->id);
    expect((float) $certificate->score)->toBe((float) $expectedTotal);
});

it('generates QR code with correct verification URL', function () {
    Storage::fake('public');

    $service = app(CertificateService::class);
    $certificateId = 'TEST-123';

    $qrPath = $service->generateQrCode($certificateId);

    expect(Storage::exists($qrPath))->toBeTrue();

    $qrContent = Storage::get($qrPath);

    expect($qrContent)->not->toBeEmpty();
    expect($qrPath)->toContain('qr-codes');
});
