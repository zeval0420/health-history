# Clinic Health History (`clinic_`)

Admin-only (clinic staff) system that records each teacher's medical history:
medications, conditions/diseases, vaccinations, allergies, procedures, visits.
Stack matches reserve-a-lab: plain PHP + MySQL (`dbadmin` database), jQuery, Bootstrap 3.3.7, DataTables.

## Principles
- **Open-ended first.** Almost every clinical field is free text. Structure is only for things that must be queried or sorted (dates, teacher link, record type).
- **Autocomplete that learns.** Every free-text value typed is stored in `clinic_lookup` (per category). Suggestions come from there, ranked by `use_count`. Admin can type anything not in the list; it gets added automatically. Seed lists (common vaccines, meds, diseases) are loaded once from `sql/seed`.
- **Rows, not forms.** Teachers are added as table rows (DataTables, inline add-row). Each teacher row expands to child tables of records.
- **Admin only.** Clinic staff login; no teacher/requester access. Health data is sensitive: audit every write, never hard-delete (soft delete).

## Teacher entry: fresh entries + autofill from reserve-a-lab
- Clinic staff add **fresh** teacher rows by typing; nothing requires a reserve-a-lab account.
- While typing a name or employee no., `ajax/clinic_ajax_teacher_lookup.php` searches reserve-a-lab's `accounts` table (**read-only**, `SELECT` only) and offers matches. Picking one autofills: `employeeID` -> `employee_no`, `lastname/firstname/middlename`, `position` -> `position`, `email`. Every autofilled field stays editable.
- Match filter: `accounts.status='active'` and `type IN ('faculty','staff')` (exclude `sysadmin`/students; confirm the `type` values against live data). Never read the `password` column.
- `clinic_teachers.employee_no` is a plain indexed column, no foreign key to `accounts`, so the clinic app keeps working if reserve-a-lab data changes. A picked teacher is **copied** into `clinic_teachers`, not referenced. Unique on `employee_no` when not empty, to prevent duplicates; fresh entries may leave it blank.
- Duplicate guard: on save, warn if the same employee_no or same last+first name already exists.

## Database
Shared connection: `include('../credentials/db_connection.php');` (outside repo; `../../credentials/` from `ajax/`). All tables prefixed `clinic_`. Migrations in `sql/`, dated and idempotent (`IF NOT EXISTS`), tracked in `schema_migrations` like reserve-a-lab, run with `mysql -u root dbadmin < sql/<file>.sql`.

| Table | Purpose | Key columns |
|---|---|---|
| `clinic_users` | Clinic admin/staff accounts | id, username, password_hash, full_name, role, active |
| `clinic_teachers` | One row per teacher | id, employee_no (nullable, unique), source (`reserve-a-lab`/`manual`), last_name, first_name, middle_name, position, email, dob, sex, department, contact, emergency_contact, blood_type, notes, deleted_at |
| `clinic_medications` | Past/current meds | teacher_id, name, dosage, frequency, reason, start_date, end_date, prescriber, notes |
| `clinic_conditions` | Diseases/illnesses/chronic conditions | teacher_id, name, status (free text), diagnosed_on, resolved_on, notes |
| `clinic_vaccinations` | Immunizations | teacher_id, vaccine, dose_label, date_given, next_due, lot_no, given_by, notes |
| `clinic_allergies` | Allergies | teacher_id, allergen, reaction, severity, notes |
| `clinic_visits` | Clinic visits / complaints | teacher_id, visit_date, complaint, assessment, action_taken, attended_by |
| `clinic_procedures` | Surgeries/hospitalizations | teacher_id, name, procedure_date, facility, notes |
| `clinic_lookup` | Autocomplete store | id, category, value, use_count, last_used (UNIQUE category+value) |
| `clinic_audit_log` | Who changed what | user_id, table_name, row_id, action, old_json, new_json, created_at |

Every record table also has: `id`, `created_by`, `created_at`, `updated_at`, `deleted_at`.
If a type isn't covered, add a generic `clinic_other_records` (teacher_id, type, title, date, details) rather than new tables.

## Autocomplete flow
1. Input gets `data-lookup="medication"` (category).
2. JS (`clinic_autocomplete.js`, debounced) calls `ajax/clinic_ajax_lookup.php?cat=medication&q=para`.
3. Returns top matches by `LIKE 'q%'` then `LIKE '%q%'`, ordered by `use_count DESC`.
4. On save, `clinic_lookup_touch($cat, $value)` upserts and increments `use_count`. Values are trimmed and case-folded for matching, original casing kept for display.

## Folder structure
```
health-history/
├── plan.md
├── README.md
├── index.php                    # clinic login
├── clinic_dashboard.php         # counts, upcoming vaccine due dates, recent visits
├── clinic_teachers.php          # teacher table (add/edit rows, search)
├── clinic_teacher.php           # one teacher: tabs for each record type
├── clinic_reports.php           # filters + print/export (vaccination due, by condition)
├── ajax/
│   ├── clinic_ajax_login.php
│   ├── clinic_ajax_teachers.php
│   ├── clinic_ajax_teacher_lookup.php  # read-only autofill from accounts
│   ├── clinic_ajax_records.php  # CRUD for all record types (type param, whitelist)
│   └── clinic_ajax_lookup.php   # autocomplete
├── helperFiles/
│   ├── clinic_session_handler.php   # session + CSRF + role check (adapt from reserve-a-lab)
│   ├── clinic_audit.php
│   ├── clinic_lookup.php            # clinic_lookup_touch(), search
│   ├── clinic_record_types.php      # config: table, fields, labels per record type
│   ├── clinic_header.php / clinic_footer.php
│   └── secrets.example.php
├── templates/                   # print views (teacher health summary)
├── sql/                         # dbadmin migrations only
│   ├── README.md                # order + run commands
│   ├── 2026-11-11_01_clinic_users.sql
│   ├── 2026-11-11_02_clinic_teachers.sql
│   ├── 2026-11-11_03_clinic_records.sql
│   ├── 2026-11-11_04_clinic_lookup_audit.sql
│   └── seed/clinic_lookup_seed.sql
├── css/  js/  assets/           # js: clinic_autocomplete.js, clinic_teachers.js
└── docs/
```
`clinic_record_types.php` drives the UI: adding a new record type = one config entry + one migration, no new page.

## Phases
1. **Foundation:** `sql/` migrations (users, teachers, schema_migrations), login, session/CSRF, header/footer.
2. **Teachers:** DataTable with add/edit/soft-delete rows, search by name/employee no., fresh entry plus `accounts` autofill.
3. **Records:** config-driven CRUD for medications, conditions, vaccinations, allergies, visits, procedures on `clinic_teacher.php`.
4. **Autocomplete:** `clinic_lookup`, endpoint, JS plugin, seed data, wire all free-text fields.
5. **Audit + reports:** audit log writes, vaccination-due list, print summary, CSV export.
6. **Hardening:** prepared statements everywhere, CSRF on all POSTs, login throttling, backup script, access review.

## Decisions
- Separate app; shares only the `dbadmin` database with reserve-a-lab.
- Teachers are entered fresh, with optional autofill copied from `accounts`.
- No data-privacy requirements specified; audit log and soft delete stay as basic good practice.

## Still to confirm
- Exact `accounts.type` values that count as teachers (`faculty` seen; `staff` too?).
- Clinic login: own `clinic_users` table (planned).
