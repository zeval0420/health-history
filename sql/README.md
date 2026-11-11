# SQL migrations (clinic_)

Run in order against the `dbadmin` database (all idempotent; shares `schema_migrations` with reserve-a-lab):

1. `2026-11-11_01_clinic_users.sql`         – `schema_migrations` (if missing), `clinic_users`
2. `2026-11-11_02_clinic_teachers.sql`      – `clinic_teachers`
3. `2026-11-11_03_clinic_records.sql`       – medications, conditions, vaccinations, allergies, visits, procedures, other
4. `2026-11-11_04_clinic_lookup_audit.sql`  – `clinic_lookup`, `clinic_audit_log`

```
for f in sql/2026-11-11_0*.sql; do mysql -u root dbadmin < "$f"; done
```

Create the first login (after migrating): `php tools/clinic_create_user.php <username> "<Full Name>"` (prompts for a password).

Naming: `YYYY-MM-DD_NN_description.sql`. Each file ends with an `INSERT IGNORE INTO schema_migrations`. Seed data goes in `sql/seed/`.

Seed data (autocomplete suggestions), after migration 04:
```
mysql -u root dbadmin < sql/seed/clinic_lookup_seed.sql
```
