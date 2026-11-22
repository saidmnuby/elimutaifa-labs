<<<<<<< HEAD
<<<<<<< HEAD
# elimutaifa-labs
=======
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
>>>>>>> e9c9bdc (NEW PROJECT THAT CONSERN WITH REMOTE DEPLOYMENT)
=======
# ElimuTaifa Labs

## What this business is

ElimuTaifa Labs is a planned hosting and project support service for independent developers. A developer should be able to register a project, connect its GitHub repository, use an assigned subdomain and (when needed) a project database, and deploy updates without receiving the main cPanel login or access to another developer's project.

The goal is to make project setup repeatable and easy to manage as more developers join. Keep each project's repository, domain, hosting folder, database, and deployment activity associated with that project.

## Product rules

- Developers must not receive the main cPanel login or another project's credentials.
- A developer can access only the project and services assigned to them.
- Hosting paths and repository details are set by the platform administrator and must be validated. Never accept an arbitrary server path from a developer workflow.
- Keep passwords, API tokens, private keys, and database credentials out of Git, this README, and logs.
- Keep GitHub tokens and private keys in GitHub/hosting secret stores. Store only project database passwords in the dedicated Laravel encrypted field; the app key must be protected and backed up. Never display or log the stored password.
- Assign each project database user only to that project's database in cPanel; the database workspace relies on those MySQL privileges to isolate projects.
- Start with administrator approval and manual checks for hosting resources. Automate resource creation only after its permissions and limits are understood.
- Add new services as controlled modules that the administrator can assign to projects. Do not execute developer-supplied scripts on the hosting account without a review and a safe design.
- Keep modules independent: a GitHub deployment request must not require database details, and a database request must not change deployment settings.
- Prefer a simple, reusable developer setup. A developer should not have to edit shared deployment code for routine onboarding.

## What has been done

### Hosting and deployment learning

- Confirmed that GitHub Actions can reach the cPanel API over HTTPS on port 2083.
- Built and used an administrator-owned deployment workflow that looks up approved project settings, checks out the approved repository, and uploads project files to its configured hosting folder through the cPanel API.
- The project registry is intended to control the allowed repository, branch, destination folder, and source-token secret name. Never let a developer choose the destination folder in their own workflow.
- Earlier cPanel Git deployment tests could report success without publishing files to the intended subdomain. Treat “deployment task succeeded” and “the live site contains the latest files” as separate checks.
- Direct FTP deployment was tested and ran into a hosting data-connection timeout. The current portal plan uses the cPanel API upload flow instead.

### This Laravel portal

- Created a Laravel 12 application named `elimutaifa-labs`.
- Added registration, login, logout, and a dashboard.
- New accounts are developers awaiting administrator approval. Administrators can activate or suspend developers.
- Active developers submit GitHub deployment requests separately with a project name, repository, branch, and requested hostname. Requests stay pending until an administrator assigns a hosting folder and approves them.
- Developers can submit multiple separate database requests for any of their non-suspended projects, including one whose deployment request is still pending. Each request has its own preferred database-name suffix and admin approval. A GitHub username is optional; when supplied, the app checks that the public account exists and blocks submission if it cannot confirm it.
- Developers can verify ownership of a GitHub account through GitHub OAuth. The connection requests only the `read:user` scope, stores the stable GitHub account ID and verified username, and does not store the access token or request repository access.
- Repository names are entered as `owner/repository` and public repositories are checked live. A private repository and a nonexistent repository are not distinguishable through the unauthenticated public check; both remain subject to admin verification.
- Developer users land directly on GitHub deployment after sign-in. Their sidebar contains only GitHub deployment and Database; there is no separate Workspace menu item. Each module has its own request and approval path.
- The Database page has a clear **Request a database** action that opens a short form to select an owned project and enter a preferred database name. It also lists project database requests/resources and provides **Open database manager** when database access is active.
- Admins have a **Database requests** queue. Each request can be approved or rejected separately. To approve, the admin registers the actual cPanel database/user and connection details, confirms that user is assigned only to that database, then activates its access independently of deployment.
- Admin navigation is in a left sidebar with Overview, Developers, Projects, and Database requests. Developer and project admin screens show totals for all, approved/active, pending, rejected, and suspended; pending developer accounts and deployment projects can be explicitly rejected, and rejected records count separately from suspended ones.
- Added a database manager for an assigned project database: list and create tables, inspect table columns, browse 25 records per page, add/edit/delete records when a single primary key exists, and delete a table after typing its name to confirm. A single-statement SQL workspace is also available; SELECT results are capped at 100 rows. Actual database creation and assignment remain administrator-managed.
- Database passwords entered by the administrator are encrypted by Laravel in the dedicated `project_databases.encrypted_password` field. They are never shown in the interface.
- GitHub OAuth confirms control of the linked GitHub account. It does not prove access to a particular repository or grant repository access; admin review and the administrator's source-read credential remain separate.
- Added initial records and screens for projects, repositories, domains, databases, and service modules.
- Added the first module definitions: `github-deploy` and `database`.
- Project records keep a database secret reference and the database password in a separate Laravel-encrypted field; never display or log that password.
- Project submission validates repository format and hostname. Only the admin supplies the hosting folder, which must be within `/home/elimutai/`.
- Added audit records for developer status changes, project submissions, and project approvals.
- Admins can edit project details, repository, branch, domain, hosting folder, service assignments, and the portal's database connection record without deleting the actual cPanel database.

## Where development stands now

This is an early local prototype, not a production-ready customer portal. The user asked to use MySQL from the start and discard SQLite data.

- The local `.env` is configured to use MySQL database `elimutaifa_labs` on XAMPP.
- The old SQLite database file was removed as requested. Do not recreate or switch back to SQLite.
- XAMPP MySQL is now running. The fresh `elimutaifa_labs` database has been created, and Laravel migrations and seeders completed successfully against MySQL; the GitHub identity columns were added on 2026-11-23.
- The first registered account was promoted from `developer/pending` to `admin/active` in the fresh local database on 2026-11-22.
- The owner reports that `db.elimutaifa.co.tz` is now online. It is a possible future web entry point for database-related services, but the portal, database tools, and access controls have not been installed or configured there yet.
- The owner account is already active as platform admin in the local database.
- Separate GitHub deployment requests, multiple database requests per project, the admin database approval/rejection queue, GitHub OAuth identity verification, module sidebars, admin resource editing, and visual database table/record management are implemented. GitHub OAuth still needs app credentials and an end-to-end authorization run.
- Deployment history integration, safe deletion/synchronization behavior, automated database provisioning, and reusable developer repository onboarding still need implementation and review.
- Review `routes/console.php`: the `platform:make-admin` command currently appears to be defined twice. Remove the duplicate before relying on the command.

## Suggested local setup

1. Start Apache and MySQL in XAMPP Control Panel. MySQL must show as running.
2. Check the local database connection settings in `.env`. Do not copy real passwords into this README or commit `.env`.
3. To enable GitHub ownership verification, create a GitHub OAuth App. Put its Client ID, Client Secret, and exact callback URL in `.env` as `GITHUB_OAUTH_CLIENT_ID`, `GITHUB_OAUTH_CLIENT_SECRET`, and `GITHUB_OAUTH_REDIRECT_URI`. The callback path is `/github/oauth/callback`; include the actual local or production base URL. The portal requests only `read:user`; it does not ask developers to grant repo access.
4. For a new local setup only, create an empty MySQL database named `elimutaifa_labs` in phpMyAdmin, then run:

   ```powershell
   C:\xampp\php\php.exe artisan migrate --seed
   ```

5. For a new local setup only, register the owner's account and promote it:

   ```powershell
   C:\xampp\php\php.exe artisan platform:make-admin owner@example.com
   ```

   Replace the email with the actual owner account. Do not put a password in this command. The current local setup already has an admin account; do not run the initial setup steps again on it.

If MySQL is unavailable, stop and fix the local MySQL startup/connection first. Do not silently use SQLite as a workaround.

## Direction and delivery stages

### Stage 1 — Stable foundation

- MySQL database, migrations, seeders, and initial admin account are set up locally.
- Verify registration, pending approval, project submission, and admin project approval in the browser.
- Fix remaining setup issues and document how to create the first admin safely.

### Stage 2 — Safe project onboarding

- Let active developers submit repository, branch, and requested domain details through GitHub deployment only; have the administrator assign the fixed hosting folder and approve the request.
- Let developers request a project database separately; show the requested prefixed name and keep it pending until the admin creates and assigns the database.
- Add clear rejection and resubmission handling for project requests.
- Provide a reusable setup for a developer repository that requests deployment when the approved branch changes.
- Keep the deployment destination and cPanel credentials exclusively in the administrator-controlled workflow and secret store.
- Give each private source repository its own least-privilege read token for the admin workflow, or later replace personal tokens with a GitHub App if practical.
- Document token expiry and rotation. A missing, expired, or revoked token should fail clearly and must never print the token.

### Stage 3 — Reliable deployment records

- Record each request, source commit, project, result, and time in the portal.
- Confirm the actual target folder and live website after a deployment; do not treat an accepted API request alone as proof that files are live.
- Define safe behavior for additions, edits, replacements, and deletions before enabling remote file deletion.
- Prevent deployments for suspended developers or inactive projects.

### Stage 4 — Project databases and additional services

- Assign a separate database and database user to each project when required.
- Keep credentials out of repositories and show them only through a deliberate, protected process.
- Keep database requests separate from deployment requests. Developers request a database from the Database module; the admin provisions a project-specific database/user and enters its connection details.
- Restrict the database workspace to the authenticated owner, a non-suspended project, and its active database module. Use the project's database user, which must have privileges only for that database.
- The database manager supports table creation/listing/deletion, table-structure viewing, record browsing and add/edit/delete, plus a single-statement SQL editor for that assigned database. Continue to review privilege boundaries and connection failures before using it with real customer data.
- Build optional modules (database, backups, monitoring, and other services) so an administrator can enable them per project.
- Begin with manual cPanel resource provisioning and record the result; automate it only when the available API and permissions are verified.

### Stage 5 — Business readiness

- Define packages, limits, pricing, support expectations, backups, and a clear suspension/offboarding process.
- Add notifications and customer-facing status only after the underlying account and project rules are reliable.
- Review access controls, input validation, audit records, token handling, and recovery procedures before using real customer projects.

## Guidance for every AI agent or developer

Before changing code:

1. Read this README and inspect the current code. This document is a project map, not proof that every feature is complete.
2. Check the current Git changes and preserve the user's existing work.
3. Confirm the active database configuration without printing credentials. MySQL is the required local database; SQLite is not an acceptable fallback.
4. Work on the next unfinished stage above. Keep changes small, explain them in plain language, and do not claim a feature works until it has been verified.
5. Do not run tests unless the user asks for tests.

Before finishing a development task, update this README in the same change:

- Move completed work from “Where development stands now” or a stage into “What has been done” only after it is actually verified.
- Update the current stage, blockers, and next immediate action.
- Record important design decisions and known limitations.
- Keep this file free of credentials, personal data, and token values.
- Do not rewrite the business direction without discussing it with the owner.

## Important project files

- `routes/web.php` — web routes.
- `app/Http/Controllers/AuthController.php` — account registration and authentication.
- `app/Http/Controllers/PlatformAdminController.php` — developer and project administration.
- `app/Http/Controllers/DashboardController.php` — dashboard data.
- `app/Models/` — project, repository, domain, database, module, deployment, audit, and user models.
- `database/migrations/` — database structure.
- `database/seeders/DatabaseSeeder.php` — initial service modules.
- `routes/console.php` — platform administrator command.
- `resources/views/admin/` — administrator screens.
- `.env` — local secrets and connection settings; never commit or copy its secret values.

## Current focus

**Verify multiple database requests end-to-end against test databases/users assigned only to one project.** Confirm each request appears separately in the admin queue, can be approved/rejected independently, and each approved database has its own manager link. Verify table creation, structure, record insert/edit/delete, table deletion confirmation, and that another developer cannot open either database. Also verify the independent GitHub deployment flow. Treat `db.elimutaifa.co.tz` as an available hosting resource for a later step; first check its HTTPS, PHP, and hosting-folder setup before deploying anything there.
>>>>>>> 821888d (this is innovation)
