# Incident Reporting and Management System

CS3SP Secure Programming coursework. Person A leads the user-facing reporting features; Person B leads authentication and administration. This starter completes the first setup milestone: a local PHP page, a MySQL connection, a draft shared schema and an incident-form preview.

## Start the project

1. Open Docker Desktop and wait until the engine is running.
2. Open PowerShell in this folder and run:

   ```powershell
   .\scripts\start.ps1
   ```

3. Open <http://localhost:8080>. Look for **Database connected** and the four incident categories.

The script generates private database passwords in `.env` on its first run. Keep that file locally; it is excluded from source control. The first build may take a few minutes. Apache/PHP and MySQL run in the `cs3sp-incident` Docker project. Only the website is exposed, on this computer's loopback address. MySQL has no published host port.

The form is a preview: it does not accept or save reports yet. There are no seeded user accounts, passwords or incident reports. Report submission needs Person B's authenticated user identity; do not substitute a hardcoded account ID.

## Files

| File or folder | Purpose |
| --- | --- |
| `public/index.php` | Starter incident-form page |
| `public/assets/styles.css` | Page styling |
| `src/database.php` | Shared PDO database connection |
| `database/schema.sql` | Tables, relationships and initial categories |
| `compose.yaml` | Local PHP/Apache and MySQL services |
| `docker/php/` | PHP image and Apache configuration |
| `scripts/start.ps1` | Create local settings, start and check the project |
| `scripts/check-setup.php` | Check the database connection and starter tables |

Only `public/` is served by Apache. Settings, SQL and PHP connection files are outside the website's document root.

## Draft database agreement with Person B

The database is `incident_reporting`. Agree these fields and values before building the two components separately:

- `users`: `id`, unique `email`, `password_hash`, `role` (`user` or `admin`), `created_at`.
- `categories`: `id`, unique `name`. Initial choices: Phishing, Cyberbullying, Suspicious activity, Other.
- `incident_reports`: `id`, `user_id`, `category_id`, `description`, `status`, `created_at`, `updated_at`.
- Report statuses: `submitted`, `under_review`, `resolved`. New reports default to `submitted`.
- Each report belongs to a user and a category through foreign keys. Deleting a referenced user or category is restricted, so report history is not silently removed.

Person A builds the reporting interface and **displays** status. Person B implements sign-in, permissions, administrator review and status **changes**. The users table is a draft integration contract, not a completed login implementation.

Database initialization runs only when the project's database volume is first created. Later changes to `schema.sql` do not update an existing database automatically. Keep `.env` with the database it configured: changing a password in that file does not change credentials inside an existing volume. Database files persist when services are stopped.

## Useful commands

Run these in the project folder:

```powershell
# Check the two services.
docker compose ps

# Recheck the database connection and tables.
docker compose exec -T web php scripts/check-setup.php

# Stop the project while preserving the database.
docker compose stop
```

If port 8080 is already occupied, change `WEB_PORT` in your local `.env`, then run the start script again and use the new port. If something fails, inspect `docker compose logs --tail 30 web database`; do not include `.env` contents when sharing an error.

## Next milestone

Agree the draft database with Person B, then connect report submission to a signed-in user. After that, implement report history, details, search and status display. The intentionally vulnerable and mitigated versions are later assessment stages; they have not been created in this starter.

## Runtime references

- [Official PHP image: installing extensions](https://hub.docker.com/_/php)
- [Official MySQL image: initial database and startup scripts](https://hub.docker.com/_/mysql)
