<?php

use App\Enums\Role;
use App\Models\Employee;
use App\Models\Period;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => Role::Admin->value,
        'nip' => '199605112025212037',
    ]);
});

/**
 * TEST 1: Admin Dashboard Loads Successfully
 */
test('admin dashboard loads successfully with all elements', function () {
    Employee::factory()->count(5)->create(['category_id' => 1]);
    Employee::factory()->count(3)->create(['category_id' => 2]);
    Period::factory()->create(['status' => 'open']);

    $this->actingAs($this->admin);

    visit('/admin')
        ->assertOk()
        ->assertSee('Admin Dashboard')
        ->assertSee('Kelola periode penilaian, import data, dan verifikasi hasil.')
        ->assertSee('Kategori 1')
        ->assertSee('Kategori 2')
        ->assertSee('Daftar Periode')
        ->assertSee('Quick Actions')
        ->assertNoConsoleErrors();
});

test('admin dashboard displays accurate statistics', function () {
    Employee::factory()->count(10)->create(['category_id' => 1]);
    Employee::factory()->count(7)->create(['category_id' => 2]);
    Period::factory()->create(['status' => 'open']);

    $this->actingAs($this->admin);

    visit('/admin')
        ->assertSee('10')
        ->assertSee('7')
        ->assertNoConsoleErrors();

    // Verify database counts match displayed stats
    expect(Employee::where('category_id', 1)->count())->toBe(10);
    expect(Employee::where('category_id', 2)->count())->toBe(7);
    expect(Period::where('status', 'open')->count())->toBe(1);
});

test('admin dashboard shows active period warning', function () {
    Period::factory()->create(['status' => 'open', 'name' => 'Active Period']);

    $this->actingAs($this->admin);

    visit('/admin')
        ->assertSee('Periode Aktif')
        ->assertSee('Pemilihan sedang berlangsung')
        ->assertSee('Active Period')
        ->assertNoConsoleErrors();
});

test('admin dashboard shows no periods message when empty', function () {
    $this->actingAs($this->admin);

    visit('/admin')
        ->assertSee('Belum ada periode yang dibuat')
        ->assertNoConsoleErrors();
});

/**
 * TEST 2: Admin Can View Periods List
 */
test('admin can view periods list with all columns', function () {
    Period::factory()->create([
        'name' => 'Period 2026 Ganjil',
        'status' => 'draft',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
    ]);

    $this->actingAs($this->admin);

    visit('/admin/periods')
        ->assertOk()
        ->assertSee('Periode')
        ->assertSee('Period 2026 Ganjil')
        ->assertSee('2026-01-01')
        ->assertSee('2026-12-31')
        ->assertNoConsoleErrors();
});

test('admin can filter periods by status - draft', function () {
    Period::factory()->create(['name' => 'Draft Period', 'status' => 'draft']);
    Period::factory()->create(['name' => 'Open Period', 'status' => 'open']);

    $this->actingAs($this->admin);

    visit('/admin/periods?status=draft')
        ->assertSee('Draft Period')
        ->assertDontSee('Open Period')
        ->assertNoConsoleErrors();
});

test('admin can filter periods by status - open', function () {
    Period::factory()->create(['name' => 'Draft Period', 'status' => 'draft']);
    Period::factory()->create(['name' => 'Open Period', 'status' => 'open']);

    $this->actingAs($this->admin);

    visit('/admin/periods?status=open')
        ->assertSee('Open Period')
        ->assertDontSee('Draft Period')
        ->assertNoConsoleErrors();
});

test('admin can filter periods by status - closed', function () {
    Period::factory()->create(['name' => 'Closed Period', 'status' => 'closed']);
    Period::factory()->create(['name' => 'Open Period', 'status' => 'open']);

    $this->actingAs($this->admin);

    visit('/admin/periods?status=closed')
        ->assertSee('Closed Period')
        ->assertDontSee('Open Period')
        ->assertNoConsoleErrors();
});

test('admin can view all periods without filter', function () {
    Period::factory()->create(['name' => 'Draft Period', 'status' => 'draft']);
    Period::factory()->create(['name' => 'Open Period', 'status' => 'open']);
    Period::factory()->create(['name' => 'Closed Period', 'status' => 'closed']);

    $this->actingAs($this->admin);

    visit('/admin/periods')
        ->assertSee('Draft Period')
        ->assertSee('Open Period')
        ->assertSee('Closed Period')
        ->assertNoConsoleErrors();
});

/**
 * TEST 3: Admin Can Create New Period
 */
test('admin can create new period with valid data', function () {
    $this->actingAs($this->admin);

    visit('/admin/periods/create')
        ->assertOk()
        ->assertSee('Buat Periode Baru')
        ->fill('name', 'Test Period E2E')
        ->select('semester', 'ganjil')
        ->fill('year', '2026')
        ->fill('start_date', '2026-01-01')
        ->fill('end_date', '2026-12-31')
        ->clickLinkOrButton('Simpan')
        ->assertPathIs('/admin/periods')
        ->assertSee('Periode berhasil dibuat')
        ->assertSee('Test Period E2E')
        ->assertNoConsoleErrors();

    $this->assertDatabaseHas('periods', [
        'name' => 'Test Period E2E',
        'semester' => 'ganjil',
        'year' => '2026',
        'status' => 'draft',
    ]);
});

test('admin can create period without optional dates', function () {
    $this->actingAs($this->admin);

    visit('/admin/periods/create')
        ->fill('name', 'Period Without Dates')
        ->select('semester', 'genap')
        ->fill('year', '2026')
        ->clickLinkOrButton('Simpan')
        ->assertPathIs('/admin/periods')
        ->assertSee('Periode berhasil dibuat')
        ->assertNoConsoleErrors();

    $this->assertDatabaseHas('periods', [
        'name' => 'Period Without Dates',
        'semester' => 'genap',
    ]);
});

/**
 * TEST 4: Admin Can Edit Period
 */
test('admin can edit draft period', function () {
    $period = Period::factory()->create([
        'name' => 'Original Name',
        'status' => 'draft',
        'semester' => 'ganjil',
        'year' => 2025,
    ]);

    $this->actingAs($this->admin);

    visit('/admin/periods')
        ->assertSee('Original Name')
        ->clickLinkOrButton('Edit')
        ->assertPathIs('/admin/periods/'.$period->id.'/edit')
        ->assertSee('Edit Periode')
        ->fill('name', 'Test Period E2E Edited')
        ->clickLinkOrButton('Simpan')
        ->assertPathIs('/admin/periods')
        ->assertSee('Periode berhasil diperbarui')
        ->assertSee('Test Period E2E Edited')
        ->assertNoConsoleErrors();

    $this->assertDatabaseHas('periods', [
        'id' => $period->id,
        'name' => 'Test Period E2E Edited',
    ]);
});

test('admin can update period with partial data', function () {
    $period = Period::factory()->create(['status' => 'draft']);

    $this->actingAs($this->admin);

    visit('/admin/periods/'.$period->id.'/edit')
        ->fill('name', 'New Name Only')
        ->clickLinkOrButton('Simpan')
        ->assertPathIs('/admin/periods')
        ->assertSee('Periode berhasil diperbarui')
        ->assertNoConsoleErrors();
});

/**
 * TEST 5: Admin Can Change Period Status
 */
test('admin can change period status from draft to open', function () {
    $period = Period::factory()->create([
        'name' => 'Status Change Test',
        'status' => 'draft',
    ]);

    $this->actingAs($this->admin);

    visit('/admin/periods/'.$period->id)
        ->assertSee('Status Change Test')
        ->assertSee('draft')
        ->clickLinkOrButton('Buka')
        ->assertPathIs('/admin/periods/'.$period->id)
        ->assertSee('Periode dibuka untuk voting')
        ->assertSee('open')
        ->assertNoConsoleErrors();

    $this->assertDatabaseHas('periods', [
        'id' => $period->id,
        'status' => 'open',
    ]);
});

test('admin can change period status from open to closed', function () {
    $period = Period::factory()->create([
        'name' => 'Close Status Test',
        'status' => 'open',
    ]);

    $this->actingAs($this->admin);

    visit('/admin/periods/'.$period->id)
        ->assertSee('Close Status Test')
        ->assertSee('open')
        ->clickLinkOrButton('Tutup')
        ->assertPathIs('/admin/periods/'.$period->id)
        ->assertSee('Periode ditutup')
        ->assertSee('closed')
        ->assertNoConsoleErrors();

    $this->assertDatabaseHas('periods', [
        'id' => $period->id,
        'status' => 'closed',
    ]);
});

test('admin can change period status from closed to announced', function () {
    $period = Period::factory()->create([
        'name' => 'Announce Status Test',
        'status' => 'closed',
    ]);

    $this->actingAs($this->admin);

    visit('/admin/periods/'.$period->id)
        ->assertSee('Announce Status Test')
        ->assertSee('closed')
        ->clickLinkOrButton('Umumkan')
        ->assertPathIs('/admin/periods/'.$period->id)
        ->assertSee('Hasil periode diumumkan')
        ->assertSee('announced')
        ->assertNoConsoleErrors();

    $this->assertDatabaseHas('periods', [
        'id' => $period->id,
        'status' => 'announced',
    ]);
});

/**
 * TEST 6: Admin Cannot Delete Active Period
 */
test('admin cannot delete open period', function () {
    $period = Period::factory()->create([
        'name' => 'Active Period',
        'status' => 'open',
    ]);

    $this->actingAs($this->admin);

    visit('/admin/periods/'.$period->id)
        ->assertSee('Active Period')
        ->clickLinkOrButton('Hapus')
        ->assertPathIs('/admin/periods/'.$period->id)
        ->assertSee('Tidak dapat menghapus periode yang sedang berlangsung')
        ->assertNoConsoleErrors();

    $this->assertDatabaseHas('periods', [
        'id' => $period->id,
        'name' => 'Active Period',
    ]);
});

test('admin cannot delete closed period', function () {
    $period = Period::factory()->create([
        'name' => 'Closed Period',
        'status' => 'closed',
    ]);

    $this->actingAs($this->admin);

    visit('/admin/periods/'.$period->id)
        ->clickLinkOrButton('Hapus')
        ->assertSee('Tidak dapat menghapus periode')
        ->assertNoConsoleErrors();

    $this->assertDatabaseHas('periods', [
        'id' => $period->id,
        'status' => 'closed',
    ]);
});

test('delete button is not visible for open periods on detail page', function () {
    $period = Period::factory()->create(['status' => 'open']);

    $this->actingAs($this->admin);

    visit('/admin/periods/'.$period->id)
        ->assertSee('Tutup')
        ->assertDontSee('Hapus')
        ->assertNoConsoleErrors();
});

/**
 * TEST 7: Admin Can Delete Draft Period
 */
test('admin can delete draft period successfully', function () {
    $period = Period::factory()->create([
        'name' => 'Period To Delete',
        'status' => 'draft',
    ]);

    $this->actingAs($this->admin);

    visit('/admin/periods')
        ->assertSee('Period To Delete')
        ->clickLinkOrButton('Hapus')
        ->assertPathIs('/admin/periods')
        ->assertSee('Periode berhasil dihapus')
        ->assertDontSee('Period To Delete')
        ->assertNoConsoleErrors();

    $this->assertDatabaseMissing('periods', [
        'id' => $period->id,
        'name' => 'Period To Delete',
    ]);
});

test('admin can delete draft period from detail page', function () {
    $period = Period::factory()->create([
        'name' => 'Draft Period Detail',
        'status' => 'draft',
    ]);

    $this->actingAs($this->admin);

    visit('/admin/periods/'.$period->id)
        ->assertSee('Draft Period Detail')
        ->clickLinkOrButton('Hapus')
        ->assertPathIs('/admin/periods')
        ->assertSee('Periode berhasil dihapus')
        ->assertNoConsoleErrors();

    $this->assertDatabaseMissing('periods', [
        'id' => $period->id,
    ]);
});

/**
 * TEST 8: Quick Actions Work
 */
test('quick action buat periode baru works from dashboard', function () {
    $this->actingAs($this->admin);

    visit('/admin')
        ->assertSee('Buat Periode Baru')
        ->clickLink('Buat Periode Baru')
        ->assertPathIs('/admin/periods/create')
        ->assertSee('Buat Periode Baru')
        ->assertNoConsoleErrors();
});

test('quick action kelola kriteria works from dashboard', function () {
    $this->actingAs($this->admin);

    visit('/admin')
        ->assertSee('Kelola Kriteria')
        ->clickLink('Kelola Kriteria')
        ->assertPathIs('/admin/criteria')
        ->assertNoConsoleErrors();
});

test('quick action import data sikep works from dashboard', function () {
    $this->actingAs($this->admin);

    visit('/admin')
        ->assertSee('Import Data SIKEP')
        ->clickLink('Import Data SIKEP')
        ->assertPathIs('/admin/sikep')
        ->assertSee('Upload')
        ->assertNoConsoleErrors();
});

test('quick action buat baru button in periods list works', function () {
    $this->actingAs($this->admin);

    visit('/admin/periods')
        ->clickLink('Buat Baru')
        ->assertPathIs('/admin/periods/create')
        ->assertNoConsoleErrors();
});

/**
 * TEST 9: Dashboard Statistics Accuracy
 */
test('dashboard statistics match database counts', function () {
    Employee::factory()->count(15)->create(['category_id' => 1]);
    Employee::factory()->count(12)->create(['category_id' => 2]);
    Period::factory()->count(3)->create(['status' => 'open']);

    $this->actingAs($this->admin);

    visit('/admin')
        ->assertSee('15')
        ->assertSee('12')
        ->assertNoConsoleErrors();

    expect(Employee::where('category_id', 1)->count())->toBe(15);
    expect(Employee::where('category_id', 2)->count())->toBe(12);
    expect(Period::where('status', 'open')->count())->toBe(3);
});

test('dashboard shows correct voting progress', function () {
    $period = Period::factory()->create(['status' => 'open']);

    $this->actingAs($this->admin);

    visit('/admin')
        ->assertSee('Progress Voting')
        ->assertNoConsoleErrors();
});

test('dashboard shows inactive status when no active period', function () {
    $this->actingAs($this->admin);

    visit('/admin')
        ->assertSee('Status Periode')
        ->assertSee('Tidak Aktif')
        ->assertNoConsoleErrors();
});

/**
 * TEST 10: SIKEP Import Link
 */
test('sikep import page loads correctly', function () {
    $this->actingAs($this->admin);

    visit('/admin/sikep')
        ->assertOk()
        ->assertSee('Upload')
        ->assertNoConsoleErrors();
});

test('sikep import link navigates correctly from dashboard', function () {
    $this->actingAs($this->admin);

    visit('/admin')
        ->clickLink('Import Data SIKEP')
        ->assertPathIs('/admin/sikep')
        ->assertNoConsoleErrors();
});

/**
 * ADDITIONAL VALIDATION TESTS
 */
test('period validation requires name', function () {
    $this->actingAs($this->admin);

    visit('/admin/periods/create')
        ->clickLinkOrButton('Simpan')
        ->assertSee('Nama periode wajib diisi')
        ->assertNoConsoleErrors();
});

test('period validation requires semester', function () {
    $this->actingAs($this->admin);

    visit('/admin/periods/create')
        ->fill('name', 'Test Period')
        ->fill('year', '2026')
        ->clickLinkOrButton('Simpan')
        ->assertSee('Semester wajib dipilih')
        ->assertNoConsoleErrors();
});

test('period validation requires year', function () {
    $this->actingAs($this->admin);

    visit('/admin/periods/create')
        ->fill('name', 'Test Period')
        ->select('semester', 'ganjil')
        ->clickLinkOrButton('Simpan')
        ->assertSee('Tahun')
        ->assertNoConsoleErrors();
});

test('period validation checks end date after start date', function () {
    $this->actingAs($this->admin);

    visit('/admin/periods/create')
        ->fill('name', 'Invalid Period')
        ->select('semester', 'ganjil')
        ->fill('year', '2026')
        ->fill('start_date', '2026-12-31')
        ->fill('end_date', '2026-01-01')
        ->clickLinkOrButton('Simpan')
        ->assertSee('Tanggal selesai harus setelah tanggal mulai')
        ->assertNoConsoleErrors();
});

test('period validation enforces minimum year', function () {
    $this->actingAs($this->admin);

    visit('/admin/periods/create')
        ->fill('name', 'Year Validation Test')
        ->select('semester', 'ganjil')
        ->fill('year', '2019')
        ->clickLinkOrButton('Simpan')
        ->assertSee('Tahun')
        ->assertNoConsoleErrors();
});

test('period validation enforces maximum year', function () {
    $this->actingAs($this->admin);

    visit('/admin/periods/create')
        ->fill('name', 'Year Validation Test')
        ->select('semester', 'ganjil')
        ->fill('year', '2101')
        ->clickLinkOrButton('Simpan')
        ->assertSee('Tahun')
        ->assertNoConsoleErrors();
});

/**
 * AUTHORIZATION TESTS
 */
test('non admin user cannot access admin dashboard', function () {
    $penilai = User::factory()->create(['role' => Role::Penilai->value]);

    $this->actingAs($penilai)
        ->visit('/admin')
        ->assertStatus(403);
});

test('guest user is redirected to login', function () {
    visit('/admin')
        ->assertPathIs('/login');
});

test('guest user cannot access periods management', function () {
    visit('/admin/periods')
        ->assertPathIs('/login');
});

/**
 * PERIOD DETAIL TESTS
 */
test('period detail displays all information correctly', function () {
    $period = Period::factory()->create([
        'name' => 'Detail Test Period',
        'semester' => 'ganjil',
        'year' => 2026,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'draft',
        'notes' => 'Test notes',
    ]);

    $this->actingAs($this->admin);

    visit('/admin/periods/'.$period->id)
        ->assertSee('Detail Test Period')
        ->assertSee('ganjil')
        ->assertSee('2026')
        ->assertSee('2026-01-01')
        ->assertSee('2026-12-31')
        ->assertSee('draft')
        ->assertNoConsoleErrors();
});

test('period detail shows correct action buttons for draft status', function () {
    $period = Period::factory()->create(['status' => 'draft']);

    $this->actingAs($this->admin);

    visit('/admin/periods/'.$period->id)
        ->assertSee('Buka')
        ->assertSee('Edit')
        ->assertSee('Hapus')
        ->assertNoConsoleErrors();
});

test('period detail shows correct action buttons for open status', function () {
    $period = Period::factory()->create(['status' => 'open']);

    $this->actingAs($this->admin);

    visit('/admin/periods/'.$period->id)
        ->assertSee('Tutup')
        ->assertDontSee('Hapus')
        ->assertNoConsoleErrors();
});

test('period detail shows correct action buttons for closed status', function () {
    $period = Period::factory()->create(['status' => 'closed']);

    $this->actingAs($this->admin);

    visit('/admin/periods/'.$period->id)
        ->assertSee('Umumkan')
        ->assertDontSee('Hapus')
        ->assertNoConsoleErrors();
});

test('period detail shows correct action buttons for announced status', function () {
    $period = Period::factory()->create(['status' => 'announced']);

    $this->actingAs($this->admin);

    visit('/admin/periods/'.$period->id)
        ->assertDontSee('Buka')
        ->assertDontSee('Tutup')
        ->assertDontSee('Umumkan')
        ->assertDontSee('Hapus')
        ->assertNoConsoleErrors();
});

/**
 * NAVIGATION TESTS
 */
test('browser back and forward buttons work correctly', function () {
    $period = Period::factory()->create();

    $this->actingAs($this->admin);

    visit('/admin/periods')
        ->clickLink('Detail')
        ->assertPathIs('/admin/periods/'.$period->id)
        ->back()
        ->assertPathIs('/admin/periods')
        ->forward()
        ->assertPathIs('/admin/periods/'.$period->id);
});

/**
 * EDGE CASES
 */
test('handles year boundary correctly', function () {
    $this->actingAs($this->admin);

    visit('/admin/periods/create')
        ->fill('name', 'Year Boundary Test')
        ->select('semester', 'ganjil')
        ->fill('year', '2026')
        ->fill('start_date', '2025-12-31')
        ->fill('end_date', '2026-01-01')
        ->clickLinkOrButton('Simpan')
        ->assertPathIs('/admin/periods')
        ->assertSee('Periode berhasil dibuat')
        ->assertNoConsoleErrors();
});

test('displays periods in correct order', function () {
    Period::factory()->create([
        'year' => 2025,
        'semester' => 'ganjil',
        'name' => '2025 Ganjil',
    ]);

    Period::factory()->create([
        'year' => 2026,
        'semester' => 'genap',
        'name' => '2026 Genap',
    ]);

    Period::factory()->create([
        'year' => 2026,
        'semester' => 'ganjil',
        'name' => '2026 Ganjil',
    ]);

    $this->actingAs($this->admin);

    visit('/admin/periods')
        ->assertSeeInOrder(['2026 Genap', '2026 Ganjil', '2025 Ganjil'])
        ->assertNoConsoleErrors();
});

test('super admin can access admin dashboard', function () {
    $superAdmin = User::factory()->create(['role' => Role::SuperAdmin->value]);

    $this->actingAs($superAdmin);

    visit('/admin')
        ->assertOk()
        ->assertSee('Admin Dashboard')
        ->assertNoConsoleErrors();
});

/**
 * STATUS BADGE STYLING
 */
test('displays status badge with correct styling for each status', function () {
    $draftPeriod = Period::factory()->create(['status' => 'draft', 'name' => 'Draft Period']);
    $openPeriod = Period::factory()->create(['status' => 'open', 'name' => 'Open Period']);
    $closedPeriod = Period::factory()->create(['status' => 'closed', 'name' => 'Closed Period']);
    $announcedPeriod = Period::factory()->create(['status' => 'announced', 'name' => 'Announced Period']);

    $this->actingAs($this->admin);

    visit('/admin/periods')
        ->assertSee('Draft Period')
        ->assertSee('Open Period')
        ->assertSee('Closed Period')
        ->assertSee('Announced Period')
        ->assertNoConsoleErrors();
});
