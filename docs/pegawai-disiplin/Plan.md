# Plan: Perbaikan Kategori "Pegawai Disiplin"

## 1. Overview

**Objective:** Memperbaiki kategori "Pegawai Disiplin" agar menampilkan daftar pegawai untuk voting.

**Approach:** Dynamic filter berdasarkan `jabatan` untuk kategori "Pegawai Disiplin", tanpa mengubah struktur database.

**Estimated Complexity:** Low - Single file modification

---

## 2. Implementation Strategy

### 2.1 Technical Approach

**Current Problem:**
```php
// VotingController.php - show() method
$employees = Employee::with('category')
    ->where('category_id', $category->id)  // ❌ No employee has category_id = 3
    ->where('id', '!=', $employeeId)
    ->whereNotIn('id', $votedEmployeeIds)
    ->get();
```

**Solution: Conditional Filter**
```php
$employeesQuery = Employee::with('category')
    ->where('id', '!=', $employeeId)
    ->whereNotIn('id', $votedEmployeeIds);

// Apply different filter based on category
if ($category->nama === 'Pegawai Disiplin') {
    // Dynamic filter by jabatan
    $employeesQuery->where('jabatan', 'not like', '%Ketua%')
                   ->where('jabatan', 'not like', '%Wakil%')
                   ->where('jabatan', 'not like', '%Panitera%')
                   ->where('jabatan', 'not like', '%Sekretaris%');
} else {
    // Standard filter by category_id
    $employeesQuery->where('category_id', $category->id)
                   ->whereNotIn('jabatan', [
                       'Ketua Pengadilan Tingkat Pertama Klas II',
                       'Wakil Ketua Tingkat Pertama',
                       'Hakim Tingkat Pertama',
                       'Panitera Tingkat Pertama Klas II',
                       'Sekretaris Tingkat Pertama Klas II',
                   ]);
}

$employees = $employeesQuery->get();
```

---

## 3. Files to Modify

### 3.1 Primary File

| File | Path | Lines to Change |
|------|------|-----------------|
| `VotingController.php` | `app/Http/Controllers/VotingController.php` | 91-102 |

### 3.2 Detailed Changes

**File:** `app/Http/Controllers/VotingController.php`

**Method:** `show(Period $period, Category $category): Response`

**Current Code (lines 77-110):**
```php
public function show(Period $period, Category $category): Response
{
    $period->load('votes');

    $criteria = $category->criteria()->orderBy('urutan')->get();

    $userId = auth()->id();
    $employeeId = auth()->user()?->employee?->id;

    $votedEmployeeIds = Vote::where('period_id', $period->id)
        ->where('voter_id', $userId)
        ->where('category_id', $category->id)
        ->pluck('employee_id');

    $employees = Employee::with('category')
        ->where('category_id', $category->id)
        ->where('id', '!=', $employeeId)
        ->whereNotIn('id', $votedEmployeeIds)
        ->whereNotIn('jabatan', [
            'Ketua Pengadilan Tingkat Pertama Klas II',
            'Wakil Ketua Tingkat Pertama',
            'Hakim Tingkat Pertama',
            'Panitera Tingkat Pertama Klas II',
            'Sekretaris Tingkat Pertama Klas II',
        ])
        ->get();

    return Inertia::render('Penilai/Voting/Show', [
        'period' => $period,
        'category' => $category,
        'criteria' => $criteria,
        'employees' => $employees,
    ]);
}
```

**New Code:**
```php
public function show(Period $period, Category $category): Response
{
    $period->load('votes');

    $criteria = $category->criteria()->orderBy('urutan')->get();

    $userId = auth()->id();
    $employeeId = auth()->user()?->employee?->id;

    $votedEmployeeIds = Vote::where('period_id', $period->id)
        ->where('voter_id', $userId)
        ->where('category_id', $category->id)
        ->pluck('employee_id');

    // Build query with conditional filter for "Pegawai Disiplin" category
    $employeesQuery = Employee::with('category')
        ->where('id', '!=', $employeeId)
        ->whereNotIn('id', $votedEmployeeIds);

    if ($category->nama === 'Pegawai Disiplin') {
        // For "Pegawai Disiplin" category: filter out pimpinan by jabatan
        $employeesQuery->where('jabatan', 'not like', '%Ketua%')
                       ->where('jabatan', 'not like', '%Wakil%')
                       ->where('jabatan', 'not like', '%Panitera%')
                       ->where('jabatan', 'not like', '%Sekretaris%');
    } else {
        // For other categories: filter by category_id
        $employeesQuery->where('category_id', $category->id)
                       ->whereNotIn('jabatan', [
                           'Ketua Pengadilan Tingkat Pertama Klas II',
                           'Wakil Ketua Tingkat Pertama',
                           'Hakim Tingkat Pertama',
                           'Panitera Tingkat Pertama Klas II',
                           'Sekretaris Tingkat Pertama Klas II',
                       ]);
    }

    $employees = $employeesQuery->get();

    return Inertia::render('Penilai/Voting/Show', [
        'period' => $period,
        'category' => $category,
        'criteria' => $criteria,
        'employees' => $employees,
    ]);
}
```

---

## 4. Testing Plan

### 4.1 Manual Testing

#### Test Case 1: Tampilkan Pegawai untuk Kategori Disiplin

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Login sebagai Penilai | Berhasil login |
| 2 | Navigate to `/penilai/voting` | Daftar kategori muncul |
| 3 | Klik kategori "Pegawai Disiplin" | Halaman voting muncul |
| 4 | Cek daftar pegawai | Daftar pegawai muncul (bukan kosong) |
| 5 | Cek apakah "Ketua" muncul | TIDAK muncul |
| 6 | Cek apakah "Wakil" muncul | TIDAK muncul |
| 7 | Cek apakah "Panitera" muncul | TIDAK muncul |
| 8 | Cek apakah "Sekretaris" muncul | TIDAK muncul |
| 9 | Cek apakah "Hakim" muncul | BOLEH muncul |

#### Test Case 2: Voting untuk Kategori Disiplin

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Pilih pegawai dari daftar | Pegawai terpilih |
| 2 | Isi nilai untuk setiap kriteria | Nilai terisi (1-100) |
| 3 | Klik submit | Success message muncul |
| 4 | Cek database | Vote tersimpan dengan category_id = 3 |
| 5 | Cek riwayat voting | Vote muncul di riwayat |

#### Test Case 3: Kategori Lain Tetap Berfungsi

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Buka kategori "Pejabat Struktural" | Pegawai dengan category_id = 1 muncul |
| 2 | Buka kategori "Non-Pejabat" | Pegawai dengan category_id = 2 muncul |
| 3 | Lakukan voting untuk kategori 1 | Voting berfungsi normal |
| 4 | Lakukan voting untuk kategori 2 | Voting berfungsi normal |

### 4.2 Automated Testing

#### Unit Test: `tests/Unit/VotingFilterTest.php` (New File)

```php
<?php

use App\Models\Category;
use App\Models\Employee;
use App\Models\Period;
use App\Models\User;

beforeEach(function () {
    $this->period = Period::factory()->create();
    $this->categoryDisiplin = Category::where('nama', 'Pegawai Disiplin')->first();
    $this->categoryPejabat = Category::where('nama', 'Pejabat Struktural/Fungsional')->first();
});

it('filters out pimpinan for pegawai disiplin category', function () {
    // Arrange
    $ketua = Employee::where('jabatan', 'like', '%Ketua%')->first();
    $hakim = Employee::where('jabatan', 'like', '%Hakim%')->first();
    $staff = Employee::where('category_id', 2)->first();

    // Act
    $controller = app(\App\Http\Controllers\VotingController::class);
    // Call show method and get employees

    // Assert
    expect($employees)->not->toContain($ketua)
        ->and($employees)->toContain($hakim) // Hakim should be included
        ->and($employees)->toContain($staff);
});

it('still uses category_id for other categories', function () {
    // Test category 1 and 2 still use category_id filter
});
```

---

## 5. Deployment Steps

### 5.1 Pre-Deployment

```bash
# 1. Backup current code
git checkout -b backup/fix-pegawai-disiplin

# 2. Run linter to check current code quality
vendor/bin/pint --dirty

# 3. Run existing tests
php artisan test --compact

# 4. Create migration note (no actual migration needed)
echo "No database migration required - logic change only"
```

### 5.2 Implementation

```bash
# 1. Edit the file
nano app/Http/Controllers/VotingController.php

# 2. Apply changes from Section 3.2

# 3. Run linter
vendor/bin/pint app/Http/Controllers/VotingController.php
```

### 5.3 Post-Deployment

```bash
# 1. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 2. Run tests
php artisan test --filter=Voting

# 3. Check logs
tail -f storage/logs/laravel.log
```

---

## 6. Verification Checklist

### Code Quality
- [ ] Code passes `vendor/bin/pint --dirty`
- [ ] No syntax errors
- [ ] Comments added for conditional logic
- [ ] Variable names are clear and descriptive

### Functionality
- [ ] Kategori "Pegawai Disiplin" menampilkan pegawai
- [ ] Pimpinan tidak muncul di daftar
- [ ] Hakim muncul di daftar
- [ ] Voting berhasil disimpan
- [ ] Kategori lain tetap berfungsi normal

### Testing
- [ ] Manual testing completed
- [ ] Unit tests pass
- [ ] Feature tests pass
- [ ] No regression bugs found

### Documentation
- [ ] Code comments updated
- [ ] Changelog updated (if applicable)
- [ ] PRD/Spec marked as implemented

---

## 7. Rollback Procedure

### If Something Goes Wrong

```bash
# 1. Revert the change
git checkout app/Http/Controllers/VotingController.php

# 2. Verify other categories still work
php artisan test --filter=Voting

# 3. Clear caches
php artisan cache:clear
```

### Rollback Decision Points

| Situation | Action |
|-----------|--------|
| Syntax error | Fix and redeploy |
| Category 1/2 broken | Rollback immediately |
| Performance degradation | Rollback and optimize |
| Test failures | Fix tests or rollback |

---

## 8. Future Improvements

### Potential Enhancements

1. **Extract to Constant:**
   ```php
   // In config/constants.php or Category model
   const PEGAWAI_DISIPLIN = 'Pegawai Disiplin';
   const PIMPINAN_PATTERNS = ['%Ketua%', '%Wakil%', '%Panitera%', '%Sekretaris%'];
   ```

2. **Database Index:**
   ```sql
   CREATE INDEX idx_employees_jabatan ON employees(jabatan);
   ```

3. **Many-to-Many Relationship:**
   - Consider for future if employees need multiple categories
   - Requires migration and more extensive changes

---

## 9. Timeline

| Task | Estimated Time |
|------|----------------|
| Code implementation | 15 minutes |
| Manual testing | 15 minutes |
| Unit/Feature tests | 30 minutes |
| Code review & fixes | 15 minutes |
| Deployment | 10 minutes |
| **Total** | **~85 minutes** |

---

## 10. Success Criteria

Implementation is considered successful when:

1. ✅ Halaman voting "Pegawai Disiplin" menampilkan daftar pegawai
2. ✅ Pimpinan (Ketua, Wakil, Panitera, Sekretaris) tidak muncul
3. ✅ Hakim boleh muncul dalam daftar
4. ✅ Voting berhasil dilakukan dan tersimpan
5. ✅ Kategori 1 dan 2 tetap berfungsi normal
6. ✅ Tidak ada error di logs
7. ✅ All tests pass

---

*End of Plan*
