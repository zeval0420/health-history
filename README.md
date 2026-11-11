# Clinic Health History

Admin-only (clinic staff) app that records each teacher's medications, conditions/diseases,
vaccinations, allergies, clinic visits, procedures and other records. Plain PHP + MySQL, sharing the
`dbadmin` database with reserve-a-lab (all tables prefixed `clinic_`). See `plan.md` for the design.

## Setup
1. `../credentials/db_connection.php` must define `$conn` (mysqli to `dbadmin`), same file reserve-a-lab uses.
2. Run the migrations and seed: see `sql/README.md`.
3. Create a login: `php tools/clinic_create_user.php admin "Clinic Admin"`.
4. Serve the folder with PHP and open `index.php`.

## Using it
- **Teachers:** add fresh entries, or autofill from reserve-a-lab accounts (copied, read-only lookup).
- **Records:** open a teacher, pick a tab (medications, conditions, ...). Fields are free text; suggestions
  come from values already typed (`clinic_lookup`) plus a seed list.
- **Reports:** vaccinations due/overdue, current medications, search by record; CSV download.
- **Print summary** per teacher.

## Adding a record type
One migration + one entry in `helperFiles/clinic_record_types.php`.

## Backup
`tools/clinic_backup.sh` dumps the `clinic_` tables to `backups/` (git-ignored). Needs `mysql` credentials via `~/.my.cnf`.

## Notes
- Records are soft-deleted; every insert/update/delete is in `clinic_audit_log`.
- All queries are prepared statements; POSTs need the session CSRF token; login is throttled per session.
- Not yet run against a live database in development: test the migrations and pages before real use.
