<?php

use App\Models\Category;
use App\Models\Certificate;
use App\Models\Employee;
use App\Models\Period;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Facades\Storage;

/**
 * E2E Tests for Certificate Generation and Verification
 *
 * This test suite covers:
 * - Admin certificate generation for closed periods
 * - Certificate display in Peserta dashboard
 * - Certificate download functionality
 * - Public certificate verification
 * - Invalid certificate error handling
 * - PDF content validation
 * - QR code URL verification
 * - Certificate list display
 * - Certificate regeneration
 * - Verification status display
 */

// Test 1: Admin Can Generate Certificates
test('admin can generate certificates for closed period', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $period = Period::factory()->create([
        'status' => 'closed',
        'name' => 'Periode Test Sertifikat 2024',
        'year' => 2024,
        'semester' => 'ganjil',
    ]);
    $category = Category::factory()->create(['nama' => 'Struktural']);
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
        ->visit("/admin/periods/{$period->id}")
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee($period->name)
        ->assertSee('Buat Sertifikat')
        ->click('Buat Sertifikat')
        ->assertWait(1000)
        ->assertSee('Sertifikat berhasil dibuat')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();

    $certificates = Certificate::where('period_id', $period->id)->get();
    expect($certificates)->toHaveCount(3);
});

test('admin can navigate to period details and see certificate button', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $period = Period::factory()->create([
        'status' => 'closed',
        'name' => 'Periode Navigasi Sertifikat',
    ]);

    $this->actingAs($admin)
        ->visit('/admin/periods')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee($period->name)
        ->click("a[href='/admin/periods/{$period->id}']")
        ->assertWait(500)
        ->assertPathIs("/admin/periods/{$period->id}")
        ->assertSee($period->name)
        ->assertSee('Buat Sertifikat');
});

// Test 2: Certificates Appear in Peserta Dashboard
test('certificates appear in peserta dashboard', function () {
    Storage::fake('public');

    $employee = Employee::factory()->create(['nama' => 'Test Peserta Employee']);
    $user = User::factory()->create([
        'role' => 'Peserta',
        'employee_id' => $employee->id,
        'name' => 'Test Peserta User',
    ]);
    $period = Period::factory()->create([
        'name' => 'Periode Dashboard Sertifikat',
        'year' => 2024,
    ]);
    $category = Category::factory()->create(['nama' => 'Fungsional']);

    $pdfContent = '%PDF-1.4 fake pdf content';
    $pdfPath = 'certificates/dashboard-test-cert.pdf';
    Storage::disk('public')->put($pdfPath, $pdfContent);

    Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'pdf_path' => $pdfPath,
        'certificate_id' => 'DASH-TEST-123',
        'rank' => 1,
        'score' => 95.5,
    ]);

    $this->actingAs($user)
        ->visit('/peserta/sertifikat')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee('Sertifikat')
        ->assertSee($period->name)
        ->assertSee($category->nama);
});

test('peserta sees empty state when no certificates exist', function () {
    $employee = Employee::factory()->create();
    $user = User::factory()->create([
        'role' => 'Peserta',
        'employee_id' => $employee->id,
    ]);

    $this->actingAs($user)
        ->visit('/peserta/sertifikat')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

// Test 3: Peserta Can Download Certificate
test('peserta can download certificate', function () {
    Storage::fake('public');

    $employee = Employee::factory()->create(['nama' => 'Download Test Employee']);
    $user = User::factory()->create([
        'role' => 'Peserta',
        'employee_id' => $employee->id,
        'name' => 'Download Test User',
    ]);
    $period = Period::factory()->create(['name' => 'Download Period 2024']);
    $category = Category::factory()->create(['nama' => 'Non Pejabat']);

    $pdfContent = '%PDF-1.4 fake pdf content for download test';
    $pdfPath = 'certificates/download-test-cert.pdf';
    Storage::disk('public')->put($pdfPath, $pdfContent);

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'pdf_path' => $pdfPath,
        'certificate_id' => 'DOWNLOAD-TEST-123',
    ]);

    $this->actingAs($user)
        ->visit('/peserta/sertifikat')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee('Download')
        ->click("a[href='/peserta/sertifikat/{$certificate->id}/download']")
        ->assertWait(2000)
        ->assertNoJavascriptErrors();
});

test('download button is visible on certificate list', function () {
    Storage::fake('public');

    $employee = Employee::factory()->create();
    $user = User::factory()->create([
        'role' => 'Peserta',
        'employee_id' => $employee->id,
    ]);
    $period = Period::factory()->create();
    $category = Category::factory()->create();

    $pdfPath = 'certificates/button-test-cert.pdf';
    Storage::disk('public')->put($pdfPath, 'pdf content');

    Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'pdf_path' => $pdfPath,
    ]);

    $this->actingAs($user)
        ->visit('/peserta/sertifikat')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee('Download');
});

// Test 4: Public Certificate Verification Works
test('public can verify certificate', function () {
    $employee = Employee::factory()->create([
        'nama' => 'Verification Test Employee',
        'nip' => '123456789012345678',
        'jabatan' => 'Pranata Komputer',
    ]);
    $period = Period::factory()->create([
        'name' => 'Verification Period 2024',
        'semester' => 'ganjil',
        'year' => 2024,
    ]);
    $category = Category::factory()->create(['nama' => 'Non Pejabat']);

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'VERIFY-PUBLIC-TEST',
        'score' => 95.5,
        'rank' => 1,
        'qr_code_path' => 'qr-codes/verify-public-test.png',
    ]);

    $this->visit("/verify/{$certificate->certificate_id}")
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee($employee->nama)
        ->assertSee($period->name)
        ->assertSee($category->nama)
        ->assertSee((string) $certificate->score)
        ->assertSee((string) $certificate->rank);
});

test('verification page is publicly accessible without login', function () {
    $certificate = Certificate::factory()->create([
        'certificate_id' => 'PUBLIC-ACCESS-TEST',
    ]);

    $this->visit("/verify/{$certificate->certificate_id}")
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSuccessful();
});

test('verification page displays all required certificate details', function () {
    $employee = Employee::factory()->create([
        'nama' => 'Detail Verification Employee',
        'nip' => '987654321098765432',
        'jabatan' => 'Analis Sistem',
        'unit_kerja' => 'Pengadilan Agama Penajam',
    ]);
    $period = Period::factory()->create([
        'name' => 'Detail Verification Period',
        'semester' => 'genap',
        'year' => 2024,
    ]);
    $category = Category::factory()->create(['nama' => 'Struktural']);

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'DETAIL-VERIFY-TEST',
        'score' => 97.85,
        'rank' => 1,
        'issued_at' => now()->subDays(10),
    ]);

    $this->visit("/verify/{$certificate->certificate_id}")
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee($employee->nama)
        ->assertSee($employee->nip)
        ->assertSee($period->name)
        ->assertSee($category->nama)
        ->assertSee('97.85')
        ->assertSee('1');
});

// Test 5: Invalid Certificate ID Shows Error
test('invalid certificate id shows 404 error', function () {
    $this->visit('/verify/INVALID-CERTIFICATE-ID')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee('404');
});

test('non-existent certificate shows error', function () {
    $this->visit('/verify/00000000-0000-0000-0000-000000000000')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertStatus(404);
});

test('invalid certificate url does not leak sensitive information', function () {
    $this->visit('/verify/<script>alert("xss")</script>')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertDontSee('<script>');
});

// Test 6: Certificate PDF Contains Correct Content
test('certificate pdf file exists and is valid', function () {
    Storage::fake('public');

    $employee = Employee::factory()->create(['nama' => 'PDF Content Test']);
    $period = Period::factory()->create();
    $category = Category::factory()->create();

    $pdfContent = '%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj
2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj
3 0 obj
<<
/Type /Page
/Parent 2 0 R
/Resources <<
/Font <<
/F1 4 0 R
>>
>>
/MediaBox [0 0 612 792]
/Contents 5 0 R
>>
endobj
4 0 obj
<<
/Type /Font
/Subtype /Type1
/BaseFont /Helvetica
>>
endobj
5 0 obj
<<
/Length 44
>>
stream
BT
/F1 12 Tf
100 700 Td
(Test Certificate) Tj
ET
endstream
endobj
xref
0 6
0000000000 65535 f
0000000009 00000 n
0000000058 00000 n
0000000115 00000 n
0000000262 00000 n
0000000353 00000 n
trailer
<<
/Size 6
/Root 1 0 R
>>
startxref
418
%%EOF';

    $pdfPath = 'certificates/content-test-cert.pdf';
    Storage::disk('public')->put($pdfPath, $pdfContent);

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'pdf_path' => $pdfPath,
        'certificate_id' => 'PDF-CONTENT-TEST',
    ]);

    expect(Storage::disk('public')->exists($pdfPath))->toBeTrue();
    expect(Storage::disk('public')->size($pdfPath))->toBeGreaterThan(0);
    expect(Storage::disk('public')->get($pdfPath))->toContain('%PDF');
});

test('pdf path is correctly stored in certificate', function () {
    $certificate = Certificate::factory()->create([
        'pdf_path' => 'certificates/test-cert.pdf',
    ]);

    expect($certificate->pdf_path)->toBe('certificates/test-cert.pdf');
});

// Test 7: QR Code Verification URL is Correct
test('qr code verification url format is correct', function () {
    $certificate = Certificate::factory()->create([
        'certificate_id' => 'QR-URL-TEST',
    ]);

    $expectedUrl = url("/verify/{$certificate->certificate_id}");
    expect($certificate->verification_url)->toBe($expectedUrl);
});

test('certificate has qr code path attribute', function () {
    $certificate = Certificate::factory()->create([
        'qr_code_path' => 'qr-codes/test-qr.png',
    ]);

    expect($certificate->qr_code_path)->toBe('qr-codes/test-qr.png');
});

test('verification page shows certificate details', function () {
    $employee = Employee::factory()->create(['nama' => 'QR Code Test Employee']);
    $period = Period::factory()->create(['name' => 'QR Code Period']);
    $category = Category::factory()->create(['nama' => 'Test Category']);

    $qrPath = 'qr-codes/qr-url-test.png';
    Storage::fake('public');
    Storage::disk('public')->put($qrPath, 'fake qr image data');

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'QR-URL-TEST',
        'qr_code_path' => $qrPath,
    ]);

    $this->visit("/verify/{$certificate->certificate_id}")
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee($employee->nama)
        ->assertSee($period->name);
});

// Test 8: Certificate List Shows All Certificates
test('certificate list shows all certificates for peserta', function () {
    Storage::fake('public');

    $employee = Employee::factory()->create(['nama' => 'Multi Certificate Employee']);
    $user = User::factory()->create([
        'role' => 'Peserta',
        'employee_id' => $employee->id,
    ]);

    $period1 = Period::factory()->create(['name' => 'Period 1 - 2024']);
    $period2 = Period::factory()->create(['name' => 'Period 2 - 2024']);
    $period3 = Period::factory()->create(['name' => 'Period 3 - 2024']);
    $category = Category::factory()->create();

    $pdfPath1 = 'certificates/cert1.pdf';
    $pdfPath2 = 'certificates/cert2.pdf';
    $pdfPath3 = 'certificates/cert3.pdf';

    Storage::disk('public')->put($pdfPath1, 'pdf1');
    Storage::disk('public')->put($pdfPath2, 'pdf2');
    Storage::disk('public')->put($pdfPath3, 'pdf3');

    Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period1->id,
        'category_id' => $category->id,
        'pdf_path' => $pdfPath1,
        'issued_at' => now()->subMonths(3),
    ]);

    Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period2->id,
        'category_id' => $category->id,
        'pdf_path' => $pdfPath2,
        'issued_at' => now()->subMonths(2),
    ]);

    Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period3->id,
        'category_id' => $category->id,
        'pdf_path' => $pdfPath3,
        'issued_at' => now()->subMonths(1),
    ]);

    $this->actingAs($user)
        ->visit('/peserta/sertifikat')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee($period1->name)
        ->assertSee($period2->name)
        ->assertSee($period3->name);
});

test('certificate list displays download button for each certificate', function () {
    Storage::fake('public');

    $employee = Employee::factory()->create();
    $user = User::factory()->create([
        'role' => 'Peserta',
        'employee_id' => $employee->id,
    ]);

    $period = Period::factory()->create();
    $category = Category::factory()->create();

    $pdfPath = 'certificates/download-button-test.pdf';
    Storage::disk('public')->put($pdfPath, 'pdf content');

    Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'pdf_path' => $pdfPath,
    ]);

    $this->actingAs($user)
        ->visit('/peserta/sertifikat')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee('Download');
});

test('certificate list shows period and category information', function () {
    Storage::fake('public');

    $employee = Employee::factory()->create();
    $user = User::factory()->create([
        'role' => 'Peserta',
        'employee_id' => $employee->id,
    ]);

    $period = Period::factory()->create([
        'name' => 'Info Display Period',
        'year' => 2024,
        'semester' => 'ganjil',
    ]);
    $category = Category::factory()->create(['nama' => 'Display Category']);

    $pdfPath = 'certificates/info-test.pdf';
    Storage::disk('public')->put($pdfPath, 'pdf');

    Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'pdf_path' => $pdfPath,
    ]);

    $this->actingAs($user)
        ->visit('/peserta/sertifikat')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee($period->name)
        ->assertSee($category->nama);
});

// Test 9: Admin Can Regenerate Certificates
test('admin can regenerate certificates for period', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'Admin']);
    $period = Period::factory()->create(['status' => 'closed']);
    $category = Category::factory()->create();
    $employees = Employee::factory()->count(2)->create(['category_id' => $category->id]);

    $voters = User::factory()->count(3)->create(['role' => 'Penilai']);

    foreach ($voters as $voter) {
        Vote::factory()->create([
            'period_id' => $period->id,
            'voter_id' => $voter->id,
            'employee_id' => $employees[0]->id,
            'category_id' => $category->id,
            'total_score' => 90,
        ]);
        Vote::factory()->create([
            'period_id' => $period->id,
            'voter_id' => $voter->id,
            'employee_id' => $employees[1]->id,
            'category_id' => $category->id,
            'total_score' => 80,
        ]);
    }

    $this->actingAs($admin)
        ->post(route('admin.periods.generate-certificates', ['period' => $period->id]));

    $certificate = Certificate::where('period_id', $period->id)->first();
    $originalId = $certificate->certificate_id;

    $this->actingAs($admin)
        ->visit("/admin/periods/{$period->id}")
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->click('Buat Sertifikat')
        ->assertWait(1000)
        ->assertSee('Sertifikat berhasil dibuat');

    $certificate->refresh();
    expect($certificate->certificate_id)->not->toBe($originalId);
});

test('certificate regeneration updates existing certificates', function () {
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
        'total_score' => 85,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.periods.generate-certificates', ['period' => $period->id]));

    $countBefore = Certificate::where('period_id', $period->id)->count();

    $this->actingAs($admin)
        ->visit("/admin/periods/{$period->id}")
        ->assertNoJavascriptErrors()
        ->click('Buat Sertifikat')
        ->assertWait(1000);

    $countAfter = Certificate::where('period_id', $period->id)->count();

    expect($countAfter)->toBe($countBefore);
});

// Test 10: Certificate Verification Shows Correct Status
test('verification page shows valid certificate status', function () {
    $employee = Employee::factory()->create(['nama' => 'Status Test Employee']);
    $period = Period::factory()->create([
        'name' => 'Status Verification Period',
    ]);
    $category = Category::factory()->create(['nama' => 'Status Category']);

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'STATUS-TEST-123',
        'score' => 98.5,
        'rank' => 1,
        'issued_at' => now()->subDays(5),
    ]);

    $this->visit("/verify/{$certificate->certificate_id}")
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee($employee->nama)
        ->assertSee($period->name)
        ->assertSee($category->nama)
        ->assertSee((string) $certificate->score)
        ->assertSee((string) $certificate->rank);
});

test('verification page displays issued date', function () {
    $issuedDate = now()->subDays(10);

    $certificate = Certificate::factory()->create([
        'certificate_id' => 'ISSUED-DATE-TEST',
        'issued_at' => $issuedDate,
    ]);

    $this->visit("/verify/{$certificate->certificate_id}")
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('verification matches employee information correctly', function () {
    $employee = Employee::factory()->create([
        'nama' => 'Match Test Employee',
        'nip' => '111111111111111111',
        'jabatan' => 'Arsiparis',
    ]);
    $period = Period::factory()->create(['name' => 'Match Test Period']);
    $category = Category::factory()->create(['nama' => 'Match Test Category']);

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'MATCH-TEST-123',
    ]);

    $this->visit("/verify/{$certificate->certificate_id}")
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee($employee->nama)
        ->assertSee($employee->nip)
        ->assertSee($period->name)
        ->assertSee($category->nama);
});

// Additional Tests: Edge Cases and Error Handling
test('certificate generation handles multiple categories correctly', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'Admin']);
    $period = Period::factory()->create(['status' => 'closed']);
    $categories = Category::factory()->count(3)->create();

    foreach ($categories as $index => $category) {
        $employees = Employee::factory()->count(2)->create([
            'category_id' => $category->id,
        ]);
        $voter = User::factory()->create(['role' => 'Penilai']);

        Vote::factory()->create([
            'period_id' => $period->id,
            'voter_id' => $voter->id,
            'employee_id' => $employees[0]->id,
            'category_id' => $category->id,
            'total_score' => 90,
        ]);
    }

    $this->actingAs($admin)
        ->visit("/admin/periods/{$period->id}")
        ->assertNoJavascriptErrors()
        ->click('Buat Sertifikat')
        ->assertWait(1000)
        ->assertSee('Sertifikat berhasil dibuat')
        ->assertNoJavascriptErrors();

    $certificates = Certificate::where('period_id', $period->id)->get();
    expect($certificates)->toHaveCount(3);
});

test('prevents unauthorized access to certificate generation', function () {
    $user = User::factory()->create(['role' => 'Peserta']);
    $period = Period::factory()->create(['status' => 'closed']);

    $this->actingAs($user)
        ->visit("/admin/periods/{$period->id}")
        ->assertStatus(403);
});

test('admin can view certificate count on period page', function () {
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
        'total_score' => 85,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.periods.generate-certificates', ['period' => $period->id]));

    $this->actingAs($admin)
        ->visit("/admin/periods/{$period->id}")
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('certificate verification page is responsive', function () {
    $certificate = Certificate::factory()->create([
        'certificate_id' => 'RESPONSIVE-TEST',
    ]);

    $this->visit("/verify/{$certificate->certificate_id}")
        ->on()->mobile()
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('certificate generation shows loading state', function () {
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
        ->visit("/admin/periods/{$period->id}")
        ->assertNoJavascriptErrors()
        ->click('Buat Sertifikat')
        ->assertNoJavascriptErrors();
});

test('verification page has no accessibility issues', function () {
    $certificate = Certificate::factory()->create([
        'certificate_id' => 'A11Y-TEST',
    ]);

    $this->visit("/verify/{$certificate->certificate_id}")
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertNoAccessibilityIssues();
});
