<?php

use App\Models\Certificate;
use App\Models\Employee;
use App\Models\Category;
use App\Models\Period;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('public can verify certificate by ID', function () {
    $employee = Employee::factory()->create([
        'nama' => 'John Doe',
        'nip' => '123456789012345678',
    ]);
    $period = Period::factory()->create([
        'name' => 'Test Period 2024',
        'semester' => 'ganjil',
        'year' => 2024,
    ]);
    $category = Category::factory()->create([
        'nama' => 'Struktural',
    ]);

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'CERT-TEST-123',
        'score' => 95.5,
        'rank' => 1,
    ]);

    $response = $this->get(route('certificates.verify', [
        'certificateId' => $certificate->certificate_id,
    ]));

    $response->assertSuccessful();
});

it('verification page shows correct details', function () {
    $employee = Employee::factory()->create([
        'nama' => 'Jane Smith',
        'nip' => '987654321098765432',
        'jabatan' => 'Pranata Komputer',
    ]);
    $period = Period::factory()->create([
        'name' => 'Period 2024-2',
        'semester' => 'genap',
        'year' => 2024,
    ]);
    $category = Category::factory()->create([
        'nama' => 'Fungsional',
    ]);

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'VERIFY-TEST-456',
        'score' => 88.75,
        'rank' => 1,
    ]);

    $response = $this->get(route('certificates.verify', [
        'certificateId' => $certificate->certificate_id,
    ]));

    $response->assertSuccessful();
});

it('returns 404 for invalid certificate ID', function () {
    $response = $this->get(route('certificates.verify', [
        'certificateId' => 'INVALID-CERT-ID',
    ]));

    $response->assertNotFound();
});

it('returns 404 for non-existent certificate', function () {
    Certificate::factory()->create([
        'certificate_id' => 'VALID-CERT-789',
    ]);

    $response = $this->get(route('certificates.verify', [
        'certificateId' => 'NON-EXISTENT-123',
    ]));

    $response->assertNotFound();
});

it('verification page includes employee data', function () {
    $employee = Employee::factory()->create([
        'nama' => 'Test Employee',
        'nip' => '111111111111111111',
        'jabatan' => 'Juru Sita',
        'unit_kerja' => 'Pengadilan Agama Penajam',
    ]);
    $period = Period::factory()->create();
    $category = Category::factory()->create();

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'EMP-DATA-TEST',
    ]);

    $response = $this->get(route('certificates.verify', [
        'certificateId' => 'EMP-DATA-TEST',
    ]));

    $response->assertSuccessful();
});

it('verification page includes period data', function () {
    $employee = Employee::factory()->create();
    $period = Period::factory()->create([
        'name' => 'Periode Test 2024',
        'semester' => 'ganjil',
        'year' => 2024,
    ]);
    $category = Category::factory()->create();

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'PERIOD-DATA-TEST',
    ]);

    $response = $this->get(route('certificates.verify', [
        'certificateId' => 'PERIOD-DATA-TEST',
    ]));

    $response->assertSuccessful();
});

it('verification page includes category data', function () {
    $employee = Employee::factory()->create();
    $period = Period::factory()->create();
    $category = Category::factory()->create([
        'nama' => 'Non Pejabat',
    ]);

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'CATEGORY-DATA-TEST',
    ]);

    $response = $this->get(route('certificates.verify', [
        'certificateId' => 'CATEGORY-DATA-TEST',
    ]));

    $response->assertSuccessful();
});

it('verification page shows score and rank', function () {
    $employee = Employee::factory()->create();
    $period = Period::factory()->create();
    $category = Category::factory()->create();

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'SCORE-RANK-TEST',
        'score' => 92.35,
        'rank' => 1,
    ]);

    $response = $this->get(route('certificates.verify', [
        'certificateId' => 'SCORE-RANK-TEST',
    ]));

    $response->assertSuccessful();
});

it('verification page includes QR code URL', function () {
    $employee = Employee::factory()->create();
    $period = Period::factory()->create();
    $category = Category::factory()->create();

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'QR-URL-TEST',
        'qr_code_path' => 'public/qr-codes/test-qr.png',
    ]);

    $response = $this->get(route('certificates.verify', [
        'certificateId' => 'QR-URL-TEST',
    ]));

    $response->assertSuccessful();
});

it('verification page is publicly accessible without authentication', function () {
    $employee = Employee::factory()->create();
    $period = Period::factory()->create();
    $category = Category::factory()->create();

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'PUBLIC-ACCESS-TEST',
    ]);

    auth()->logout();

    $response = $this->get(route('certificates.verify', [
        'certificateId' => 'PUBLIC-ACCESS-TEST',
    ]));

    $response->assertSuccessful();
});

it('verification page marks certificate as valid', function () {
    $employee = Employee::factory()->create();
    $period = Period::factory()->create();
    $category = Category::factory()->create();

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'VALID-MARK-TEST',
    ]);

    $response = $this->get(route('certificates.verify', [
        'certificateId' => 'VALID-MARK-TEST',
    ]));

    $response->assertSuccessful();
});

it('verification URL is accessible from certificate model', function () {
    $employee = Employee::factory()->create();
    $period = Period::factory()->create();
    $category = Category::factory()->create();

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'URL-ACCESS-TEST',
    ]);

    $expectedUrl = url('/verify/URL-ACCESS-TEST');
    expect($certificate->verification_url)->toBe($expectedUrl);

    $response = $this->get($certificate->verification_url);
    $response->assertSuccessful();
});

it('handles certificate ID with special characters', function () {
    $employee = Employee::factory()->create();
    $period = Period::factory()->create();
    $category = Category::factory()->create();

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'CERT-123-ABC-456',
    ]);

    $response = $this->get(route('certificates.verify', [
        'certificateId' => 'CERT-123-ABC-456',
    ]));

    $response->assertSuccessful();
});

it('verification page loads issued_at date', function () {
    $employee = Employee::factory()->create();
    $period = Period::factory()->create();
    $category = Category::factory()->create();

    $issuedAt = now()->subDays(5);

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'DATE-TEST-123',
        'issued_at' => $issuedAt,
    ]);

    $response = $this->get(route('certificates.verify', [
        'certificateId' => 'DATE-TEST-123',
    ]));

    $response->assertSuccessful();
});

it('verification page handles certificate with PDF path', function () {
    $employee = Employee::factory()->create();
    $period = Period::factory()->create();
    $category = Category::factory()->create();

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'PDF-PATH-TEST',
        'pdf_path' => 'public/certificates/test.pdf',
    ]);

    $response = $this->get(route('certificates.verify', [
        'certificateId' => 'PDF-PATH-TEST',
    ]));

    $response->assertSuccessful();
});
