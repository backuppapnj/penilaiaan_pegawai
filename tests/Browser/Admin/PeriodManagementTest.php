<?php

use App\Enums\Role;
use App\Models\Period;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => Role::Admin->value]);
});

it('completes create period flow successfully', function () {
    $this->actingAs($this->admin);

    visit(route('admin.periods.index'))
        ->assertSee('Periode')
        ->clickLinkOrButton('Buat Periode Baru')
        ->assertPathIs('/admin/periods/create')
        ->assertSee('Buat Periode Baru')
        ->fill('name', 'Penilaian Browser Test 2026')
        ->select('semester', 'ganjil')
        ->fill('year', '2026')
        ->fill('start_date', '2026-01-01')
        ->fill('end_date', '2026-12-31')
        ->clickLinkOrButton('Simpan')
        ->assertPathIs('/admin/periods')
        ->assertSee('Periode berhasil dibuat')
        ->assertSee('Penilaian Browser Test 2026');
});

it('validates required fields when creating period', function () {
    $this->actingAs($this->admin);

    visit(route('admin.periods.create'))
        ->clickLinkOrButton('Simpan')
        ->assertSee('Nama periode wajib diisi');
});

it('validates end_date after start_date when creating period', function () {
    $this->actingAs($this->admin);

    visit(route('admin.periods.create'))
        ->fill('name', 'Invalid Period')
        ->select('semester', 'ganjil')
        ->fill('year', '2026')
        ->fill('start_date', '2026-12-31')
        ->fill('end_date', '2026-01-01')
        ->clickLinkOrButton('Simpan')
        ->assertSee('Tanggal selesai harus setelah tanggal mulai');
});

it('completes edit period flow successfully', function () {
    $period = Period::factory()->create([
        'name' => 'Original Period Name',
        'status' => 'draft',
        'semester' => 'ganjil',
        'year' => 2025,
    ]);

    $this->actingAs($this->admin);

    visit(route('admin.periods.index'))
        ->assertSee('Original Period Name')
        ->clickLinkOrButton('Edit')
        ->assertPathIs('/admin/periods/' . $period->id . '/edit')
        ->assertSee('Edit Periode')
        ->fill('name', 'Updated Period Name')
        ->select('semester', 'genap')
        ->fill('year', '2026')
        ->clickLinkOrButton('Simpan')
        ->assertPathIs('/admin/periods')
        ->assertSee('Periode berhasil diperbarui')
        ->assertSee('Updated Period Name');
});

it('validates end_date when editing period', function () {
    $period = Period::factory()->create(['status' => 'draft']);

    $this->actingAs($this->admin);

    visit(route('admin.periods.edit', $period))
        ->fill('name', 'Updated Period')
        ->select('semester', 'ganjil')
        ->fill('year', '2026')
        ->fill('start_date', '2026-12-31')
        ->fill('end_date', '2026-01-01')
        ->clickLinkOrButton('Simpan')
        ->assertSee('Tanggal selesai harus setelah tanggal mulai');
});

it('completes status change to open flow', function () {
    $period = Period::factory()->create([
        'name' => 'Status Change Test',
        'status' => 'draft',
    ]);

    $this->actingAs($this->admin);

    visit(route('admin.periods.show', $period))
        ->assertSee('Status Change Test')
        ->assertSee('draft')
        ->clickLinkOrButton('Buka')
        ->assertPathIs('/admin/periods/' . $period->id)
        ->assertSee('Periode dibuka untuk voting')
        ->assertSee('open');
});

it('completes status change to closed flow', function () {
    $period = Period::factory()->create([
        'name' => 'Close Status Test',
        'status' => 'open',
    ]);

    $this->actingAs($this->admin);

    visit(route('admin.periods.show', $period))
        ->assertSee('Close Status Test')
        ->assertSee('open')
        ->clickLinkOrButton('Tutup')
        ->assertPathIs('/admin/periods/' . $period->id)
        ->assertSee('Periode ditutup')
        ->assertSee('closed');
});

it('completes status change to announced flow', function () {
    $period = Period::factory()->create([
        'name' => 'Announce Status Test',
        'status' => 'closed',
    ]);

    $this->actingAs($this->admin);

    visit(route('admin.periods.show', $period))
        ->assertSee('Announce Status Test')
        ->assertSee('closed')
        ->clickLinkOrButton('Umumkan')
        ->assertPathIs('/admin/periods/' . $period->id)
        ->assertSee('Hasil periode diumumkan')
        ->assertSee('announced');
});

it('completes delete draft period flow', function () {
    $period = Period::factory()->create([
        'name' => 'Period To Delete',
        'status' => 'draft',
    ]);

    $this->actingAs($this->admin);

    visit(route('admin.periods.index'))
        ->assertSee('Period To Delete')
        ->clickLinkOrButton('Hapus')
        ->assertPathIs('/admin/periods')
        ->assertSee('Periode berhasil dihapus')
        ->assertDontSee('Period To Delete');
});

it('prevents deleting open period with error message', function () {
    $period = Period::factory()->create([
        'name' => 'Active Period',
        'status' => 'open',
    ]);

    $this->actingAs($this->admin);

    visit(route('admin.periods.show', $period))
        ->assertSee('Active Period')
        ->clickLinkOrButton('Hapus')
        ->assertPathIs('/admin/periods/' . $period->id)
        ->assertSee('Tidak dapat menghapus periode yang sedang berlangsung');

    $this->assertDatabaseHas('periods', [
        'id' => $period->id,
        'name' => 'Active Period',
    ]);
});

it('displays period list with correct ordering', function () {
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

    visit(route('admin.periods.index'))
        ->assertSeeInOrder(['2026 Genap', '2026 Ganjil', '2025 Ganjil']);
});

it('filters periods by status', function () {
    Period::factory()->create([
        'name' => 'Draft Period',
        'status' => 'draft',
    ]);

    Period::factory()->create([
        'name' => 'Open Period',
        'status' => 'open',
    ]);

    Period::factory()->create([
        'name' => 'Closed Period',
        'status' => 'closed',
    ]);

    Period::factory()->create([
        'name' => 'Announced Period',
        'status' => 'announced',
    ]);

    $this->actingAs($this->admin);

    visit(route('admin.periods.index', ['status' => 'draft']))
        ->assertSee('Draft Period')
        ->assertDontSee('Open Period');

    visit(route('admin.periods.index', ['status' => 'open']))
        ->assertSee('Open Period')
        ->assertDontSee('Draft Period');
});

it('displays period detail with all information', function () {
    $period = Period::factory()->create([
        'name' => 'Detail Test Period',
        'semester' => 'ganjil',
        'year' => 2026,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'status' => 'draft',
    ]);

    $this->actingAs($this->admin);

    visit(route('admin.periods.show', $period))
        ->assertSee('Detail Test Period')
        ->assertSee('ganjil')
        ->assertSee('2026')
        ->assertSee('2026-01-01')
        ->assertSee('2026-12-31')
        ->assertSee('draft');
});

it('shows correct action buttons based on period status', function () {
    $draftPeriod = Period::factory()->create(['status' => 'draft']);
    $openPeriod = Period::factory()->create(['status' => 'open']);

    $this->actingAs($this->admin);

    visit(route('admin.periods.show', $draftPeriod))
        ->assertSee('Buka')
        ->assertSee('Edit')
        ->assertSee('Hapus');

    visit(route('admin.periods.show', $openPeriod))
        ->assertSee('Tutup')
        ->assertDontSee('Hapus');
});

it('redirects non-admin users attempting to access periods', function () {
    $penilai = User::factory()->create(['role' => Role::Penilai->value]);

    $this->actingAs($penilai)
        ->visit(route('admin.periods.index'))
        ->assertStatus(403);
});

it('requires authentication for period management', function () {
    visit(route('admin.periods.index'))
        ->assertPathIs('/login');
});

it('allows creating period without optional dates', function () {
    $this->actingAs($this->admin);

    visit(route('admin.periods.create'))
        ->fill('name', 'Period Without Dates')
        ->select('semester', 'genap')
        ->fill('year', '2026')
        ->clickLinkOrButton('Simpan')
        ->assertPathIs('/admin/periods')
        ->assertSee('Periode berhasil dibuat')
        ->assertSee('Period Without Dates');
});

it('updates period with partial data', function () {
    $period = Period::factory()->create([
        'name' => 'Original Name',
        'status' => 'draft',
    ]);

    $this->actingAs($this->admin);

    visit(route('admin.periods.edit', $period))
        ->fill('name', 'New Name Only')
        ->clickLinkOrButton('Simpan')
        ->assertPathIs('/admin/periods')
        ->assertSee('Periode berhasil diperbarui')
        ->assertSee('New Name Only');
});

it('handles year boundary correctly', function () {
    $this->actingAs($this->admin);

    visit(route('admin.periods.create'))
        ->fill('name', 'Year Boundary Test')
        ->select('semester', 'ganjil')
        ->fill('year', '2026')
        ->fill('start_date', '2025-12-31')
        ->fill('end_date', '2026-01-01')
        ->clickLinkOrButton('Simpan')
        ->assertPathIs('/admin/periods')
        ->assertSee('Periode berhasil dibuat');
});

it('displays error for minimum year validation', function () {
    $this->actingAs($this->admin);

    visit(route('admin.periods.create'))
        ->fill('name', 'Year Validation Test')
        ->select('semester', 'ganjil')
        ->fill('year', '2019')
        ->clickLinkOrButton('Simpan')
        ->assertSee('Tahun');
});

it('displays error for maximum year validation', function () {
    $this->actingAs($this->admin);

    visit(route('admin.periods.create'))
        ->fill('name', 'Year Validation Test')
        ->select('semester', 'ganjil')
        ->fill('year', '2101')
        ->clickLinkOrButton('Simpan')
        ->assertSee('Tahun');
});

it('validates semester selection', function () {
    $this->actingAs($this->admin);

    visit(route('admin.periods.create'))
        ->fill('name', 'Semester Test')
        ->fill('year', '2026')
        ->clickLinkOrButton('Simpan')
        ->assertSee('Semester wajib dipilih');
});

it('allows super admin to manage periods', function () {
    $superAdmin = User::factory()->create(['role' => Role::SuperAdmin->value]);

    $this->actingAs($superAdmin);

    visit(route('admin.periods.index'))
        ->assertOk()
        ->assertSee('Periode');
});

it('displays status badge with correct styling', function () {
    $period = Period::factory()->create([
        'name' => 'Status Badge Test',
        'status' => 'draft',
    ]);

    $this->actingAs($this->admin);

    visit(route('admin.periods.show', $period))
        ->assertSee('Status Badge Test')
        ->assertElementExists('span'); // Status badge element
});
