# Implementation Plan: Sistem Penilaian Pegawai Terbaik PA Penajam

## Phase 1: Database & Models

### 1.1 Create Migrations
- [ ] Create users table (with roles)
- [ ] Create employees table
- [ ] Create categories table
- [ ] Create criteria table
- [ ] Create periods table
- [ ] Create votes table
- [ ] Create vote_details table (per kriteria)
- [ ] Create discipline_scores table
- [ ] Create certificates table
- [ ] Create audit_logs table

### 1.2 Create Models
- [ ] User model with roles enum
- [ ] Employee model
- [ ] Category model
- [ ] Criterion model
- [ ] Period model
- [ ] Vote model with relationships
- [ ] VoteDetail model
- [ ] DisciplineScore model
- [ ] Certificate model
- [ ] AuditLog model

### 1.3 Create Factories & Seeders
- [ ] UserFactory with role assignment
- [ ] EmployeeFactory
- [ ] CategorySeeder (3 categories)
- [ ] CriterionSeeder (criteria per category)
- [ ] EmployeeSeeder from JSON

### 1.4 Task: Conductor - User Manual Verification 'Phase 1'
- [ ] Verify migrations run successfully
- [ ] Verify models have correct relationships
- [ ] Verify seeders import data correctly
- [ ] Verify database schema matches spec

---

## Phase 2: Authentication & Authorization

### 2.1 Setup Laravel Fortify
- [ ] Install Fortify
- [ ] Configure Fortify views (Inertia)
- [ ] Configure authentication routes

### 2.2 Implement Role System
- [ ] Create Role enum (SuperAdmin, Admin, Penilai, Peserta)
- [ ] Add role column to users
- [ ] Create role middleware
- [ ] Create permission gates

### 2.3 Create Authentication Pages
- [ ] Login page (React)
- [ ] Forgot password
- [ ] Reset password

### 2.4 Implement Role-Based Dashboard Redirect
- [ ] Super Admin → /super-admin
- [ ] Admin → /admin
- [ ] Penilai → /penilai
- [ ] Peserta → /peserta

### 2.5 Task: Conductor - User Manual Verification 'Phase 2'
- [ ] Verify login works with NIP
- [ ] Verify role-based redirect works
- [ ] Verify unauthorized access is blocked
- [ ] Verify all 4 roles can login

---

## Phase 3: Employee Management

### 3.1 Import Data from JSON
- [ ] Create Artisan command to import from data_pegawai.json
- [ ] Create Artisan command to categorize from org_structure.json
- [ ] Parse JSON and create employees
- [ ] Assign category based on org structure
- [ ] Assign role to users

### 3.2 Employee Management Pages
- [ ] Employee index page (Admin only)
- [ ] Employee detail page
- [ ] Edit employee (Admin only)

### 3.3 Task: Conductor - User Manual Verification 'Phase 3'
- [ ] Verify JSON import works
- [ ] Verify categorization is correct
- [ ] Verify all 29 employees imported
- [ ] Verify category assignment matches spec

---

## Phase 4: Voting System (Category 1 & 2)

### 4.1 Period Management
- [ ] Create PeriodController
- [ ] Index page (list periods)
- [ ] Create period form
- [ ] Update period status (draft → open → closed → announced)
- [ ] Delete period (Admin only)

### 4.2 Criteria Management
- [ ] CriteriaController (Admin only)
- [ ] Index page (list criteria by category)
- [ ] Create/edit criteria
- [ ] Update weights

### 4.3 Voting Interface
- [ ] VotingController
- [ ] Voting page (Penilai)
- [ ] Filter by category
- [ ] Form to rate each criterion (1-100)
- [ ] Validation: cannot vote for self
- [ ] Submit vote

### 4.4 Vote Processing
- [ ] Calculate scores per category
- [ ] Rank by score
- [ ] Determine winner (1 per category)

### 4.5 Task: Conductor - User Manual Verification 'Phase 4'
- [ ] Verify Admin can create period
- [ ] Verify voting form works
- [ ] Verify cannot vote for self
- [ ] Verify scores calculate correctly
- [ ] Verify winner determination works

---

## Phase 5: Discipline Score (Category 3 - SIKEP)

### 5.1 Excel Import
- [ ] Install DomPDF for certificates
- [ ] Create ExcelImportController
- [ ] Upload form (Admin only)
- [ ] Parse Excel rekap kehadiran
- [ ] Map columns to database fields
- [ ] Calculate discipline scores

### 5.2 Score Calculation Logic
- [ ] Implement formula for attendance score (50%)
- [ ] Implement formula for discipline score (35%)
- [ ] Implement formula for compliance score (15%)
- [ ] Store calculated scores

### 5.3 Task: Conductor - User Manual Verification 'Phase 5'
- [ ] Verify Excel upload works
- [ ] Verify all columns parsed correctly
- [ ] Verify score calculation is accurate
- [ ] Verify scores saved to database

---

## Phase 6: Dashboard & Results

### 6.1 Admin Dashboard
- [ ] Dashboard statistics
- [ ] Active period management
- [ ] Quick actions (open/close period)
- [ ] Results preview (before announcement)

### 6.2 Penilai Dashboard
- [ ] List of employees to rate
- [ ] Voting status (completed/pending)
- [ ] View own votes

### 6.3 Peserta Dashboard
- [ ] View results (after announcement)
- [ ] Download certificate (if winner)
- [ ] View own ranking

### 6.4 Super Admin Dashboard
- [ ] All Admin features
- [ ] Audit trail viewer
- [ ] System configuration

### 6.5 Results Page
- [ ] Public results (after announcement)
- [ ] Filter by category
- [ ] Show winner details

### 6.6 Task: Conductor - User Manual Verification 'Phase 6'
- [ ] Verify all dashboards load correctly
- [ ] Verify role-based access
- [ ] Verify results display correctly
- [ ] Verify certificates downloadable by winners

---

## Phase 7: Certificate Generation

### 7.1 Install Dependencies
- [ ] Install DomPDF
- [ ] Install QR Code generator

### 7.2 Certificate Template
- [ ] Create HTML template
- [ ] Add PA Penajam styling
- [ ] Add placeholder for dynamic content

### 7.3 Certificate Generation Logic
- [ ] Create CertificateController
- [ ] Generate PDF for winners
- [ ] Add QR code with verification URL
- [ ] Store certificate in database

### 7.4 Certificate Verification
- [ ] Public verification route
- [ ] Verify certificate by ID
- [ ] Show certificate details

### 7.5 Task: Conductor - User Manual Verification 'Phase 7'
- [ ] Verify PDF generates correctly
- [ ] Verify QR code works
- [ ] Verify verification route works
- [ ] Verify certificate styling is correct

---

## Phase 8: Audit Trail

### 8.1 Logging System
- [ ] Create AuditLog service
- [ ] Log all authentication events
- [ ] Log all voting events
- [ ] Log all admin actions
- [ ] Log SIKEP imports

### 8.2 Audit Log Viewer
- [ ] Index page (Super Admin only)
- [ ] Filter by user/action/date
- [ ] Export to CSV/PDF

### 8.3 Task: Conductor - User Manual Verification 'Phase 8'
- [ ] Verify all actions are logged
- [ ] Verify logs are accurate
- [ ] Verify export works
- [ ] Verify only Super Admin can view

---

## Phase 9: Testing

### 9.1 Feature Tests
- [ ] Test authentication
- [ ] Test role-based access
- [ ] Test voting system
- [ ] Test SIKEP import
- [ ] Test certificate generation

### 9.2 Browser Tests (Critical Paths)
- [ ] Test login flow
- [ ] Test voting flow
- [ ] Test results viewing
- [ ] Test certificate download

### 9.3 Bug Fixes
- [ ] Fix any identified issues
- [ ] Re-test after fixes

### 9.4 Task: Conductor - User Manual Verification 'Phase 9'
- [ ] Verify all tests pass
- [ ] Verify critical user flows work
- [ ] Verify no console errors
- [ ] Verify deployment readiness

---

## Phase 10: Deployment

### 10.1 Pre-Deployment
- [ ] Run Pint for code formatting
- [ ] Run all tests
- [ ] Check environment configuration

### 10.2 Deployment
- [ ] Deploy to server
- [ ] Run migrations
- [ ] Seed initial data
- [ ] Verify deployment

### 10.3 Post-Deployment
- [ ] Create first admin user
- [ ] Import employee data
- [ ] Create first period
- [ ] User acceptance testing

### 10.4 Task: Conductor - User Manual Verification 'Phase 10'
- [ ] Verify deployment successful
- [ ] Verify all features work in production
- [ ] Verify performance is acceptable
- [ ] Document any known issues

---

## Notes

- **Priority**: MVP features first (Phase 1-6)
- **Timeline**: Deadline besok - focus on core functionality
- **Testing**: Critical paths only for MVP
- **Post-MVP**: SIKEP import, certificates, audit trail can be Phase 2
