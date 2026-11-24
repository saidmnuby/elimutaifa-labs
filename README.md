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
- Deployment destination and branch come from the administrator-approved portal record. Never accept a destination folder from a developer-controlled workflow.
- Earlier cPanel Git deployment tests could report success without publishing files to the intended subdomain. Treat “deployment task succeeded” and “the live site contains the latest files” as separate checks.
- Direct FTP deployment was tested and ran into a hosting data-connection timeout. The current portal plan uses the cPanel API upload flow instead.

### This Laravel portal

- Created a Laravel 12 application named `elimutaifa-labs`.
- Added registration, login, logout, and a dashboard.
- New accounts are developers awaiting administrator approval. Administrators can activate or suspend developers.
- Active developers submit GitHub deployment requests separately with a project name, repository, branch, and requested hostname. Requests stay pending until an administrator assigns a hosting folder and approves them.
- Developers can submit multiple separate database requests for any of their non-suspended projects, including one whose deployment request is still pending. Each request has its own preferred database-name suffix and admin approval.
- Developers can verify ownership of a GitHub account through GitHub App OAuth. The connection requests only the `read:user` scope, stores the stable GitHub account ID and verified username, and does not store the user access token.
- Repository access is verified separately through the ElimuTaifa Labs GitHub App. A developer installs the App on selected repositories; the portal uses a short-lived, read-only installation token to list accessible repositories. A project request is rejected unless the selected repository is accessible to that installation. The installation ID is saved with the repository for later deployment integration.
- When a developer returns to connect a repository, the portal checks whether the App is already installed for their verified personal GitHub account and reloads its current allowed repository list. This avoids making the user repeat or save an unchanged GitHub installation screen. Organization installations still use the GitHub installation callback.
- The project submission form now presents identity verification, GitHub App repository access, and project details as three separate steps. The project details step offers the repositories returned by GitHub so developers can select a verified repository instead of typing its name.
- Developer users land directly on GitHub deployment after sign-in. Their sidebar contains only GitHub deployment and Database; there is no separate Workspace menu item. Each module has its own request and approval path.
- The Database page has a clear **Request a database** action that opens a short form to select an owned project and enter a preferred database name. It also lists project database requests/resources and provides **Open database manager** when database access is active.
- Admins have a **Database requests** queue. Each request can be approved or rejected separately. To approve, the admin registers the actual cPanel database/user and connection details, confirms that user is assigned only to that database, then activates its access independently of deployment.
- Admin navigation is in a left sidebar with Overview, Developers, Projects, and Database requests. Developer and project admin screens show totals for all, approved/active, pending, rejected, and suspended; pending developer accounts and deployment projects can be explicitly rejected, and rejected records count separately from suspended ones.
- Added a database manager for an assigned project database: list and create tables, inspect table columns, browse 25 records per page, add/edit/delete records when a single primary key exists, and delete a table after typing its name to confirm. A single-statement SQL workspace is also available; SELECT results are capped at 100 rows. Actual database creation and assignment remain administrator-managed.
- Database passwords entered by the administrator are encrypted by Laravel in the dedicated `project_databases.encrypted_password` field. They are never shown in the interface.
- GitHub OAuth confirms control of the linked GitHub account. GitHub App installation access separately verifies that the App can read a selected repository. Admin project approval and deployment credentials remain separate.
- Added initial records and screens for projects, repositories, domains, databases, and service modules.
- Added the first module definitions: `github-deploy` and `database`.
- Project records keep a database secret reference and the database password in a separate Laravel-encrypted field; never display or log that password.
- Project submission validates repository format and hostname. Only the admin supplies the hosting folder, which must be within `/home/elimutai/`.
- Added audit records for developer status changes, project submissions, and project approvals.
- Admins can edit project details, repository, branch, domain, hosting folder, service assignments, and the portal's database connection record without deleting the actual cPanel database.

## Where development stands now

This is an early customer-portal prototype, deployed for live development but not yet production-ready for customers. The user asked to use MySQL from the start and discard SQLite data.

- The local `.env` is configured to use MySQL database `elimutaifa_labs` on XAMPP.
- The old SQLite database file was removed as requested. Do not recreate or switch back to SQLite.
- XAMPP MySQL is now running. The fresh `elimutaifa_labs` database has been created, and Laravel migrations and seeders completed successfully against MySQL; the GitHub identity columns were added on 2026-11-23.
- The first registered account was promoted from `developer/pending` to `admin/active` in the fresh local database on 2026-11-22.
- The owner reports that `db.elimutaifa.co.tz` is online and the Laravel portal is deployed there. The production `.env`, application key, MySQL connection, and migrations have been configured; the owner reports that the system is currently working.
- The admin registry now has a separate `elimutaifa-labs` entry targeting `db.elimutaifa.co.tz`; its JSON is valid and its repository/branch match this repository's deployment trigger.
- For future deployments, confirm the domain document root remains Laravel's `public/` folder. The admin upload workflow does not run `composer install`, and this repository excludes `/vendor`.
- The owner account is already active as platform admin in the local database.
- Separate GitHub deployment requests, multiple database requests per project, the admin database approval/rejection queue, GitHub OAuth identity verification, module sidebars, admin resource editing, and visual database table/record management are implemented.
- GitHub App `ElimuTaifa Labs` is registered and configured. The setup callback is `/github/app/installed`; OAuth during installation is disabled so the setup callback can receive the installation ID. The App private key is stored outside the web root on cPanel with restrictive file permissions. App credentials are configured in the production environment, configuration cache was cleared, and the repository installation migration ran successfully. The owner reports that GitHub repository verification succeeded.
- A clearer three-step project submission screen has been implemented locally after the App verification flow was confirmed. This UI update still needs deployment and a browser check.
- A SQL backup download and a GitHub push-webhook deployment flow have been implemented locally but are not yet deployed. The webhook checks its GitHub signature, matches the registered repository and installation, and queues deployment only for an approved active project on its approved branch and hosting path. It records the delivery ID to prevent duplicate deployments, then sends the exact commit and approved resources to the admin workflow. A signed callback records the final deployment result.
- The new GitHub deployment flow removes the need for workflow YAML and deployment tokens inside developer repositories. It uses one server-side dispatch token limited to the admin deployment repository, plus a short-lived, read-only GitHub App installation token in the central workflow to fetch each private source repository.
- The matching central workflow template is currently at `C:/Users/SAID_MNUBY/Documents/Codex/mitandao-deploy.yml`; it must be copied to `saidmnuby/mitandao/.github/workflows/deploy.yml`. It expects the admin repository variable `ELIMUTAIFA_LABS_CLIENT_ID`, secrets `ELIMUTAIFA_LABS_APP_PRIVATE_KEY` and `ELIMUTAIFA_LABS_CALLBACK_SECRET`, and variable `ELIMUTAIFA_LABS_CALLBACK_URL`. Existing cPanel secrets remain in the admin repository.
- Before the webhook flow can run in production, set `GITHUB_APP_WEBHOOK_SECRET`, `GITHUB_DEPLOY_DISPATCH_TOKEN`, and `GITHUB_DEPLOY_CALLBACK_SECRET` in the portal `.env`; set the GitHub App webhook URL to `/github/webhook` with the same webhook secret and subscribe to Push events; and run the new deployments migration. The callback secret must also be set in the admin repository Actions secrets.
- The owner reported that the active `elimutai_words_game` database can be opened and lists zero tables, but manual table creation and SQL commands fail, and the Create Table page returns HTTP 500. The log identified the HTTP 500 cause: `DatabaseTableController` referenced `Project::STATUS_SUSPENDED` without importing `App\Models\Project`. The missing import is fixed locally. SQL command failures are separately logged locally, but still need a fresh reproduction after deployment to identify their cause.
- The new webhook deployment path needs production configuration and a live end-to-end run before it can be called complete. Existing deployment history status views, safe remote deletion/synchronization, and automated database provisioning still need implementation and review.
- Review `routes/console.php`: the `platform:make-admin` command currently appears to be defined twice. Remove the duplicate before relying on the command.

## Suggested local setup

1. Start Apache and MySQL in XAMPP Control Panel. MySQL must show as running.
2. Check the local database connection settings in `.env`. Do not copy real passwords into this README or commit `.env`.
3. To enable GitHub identity and repository verification, configure the GitHub App credentials in `.env`: `GITHUB_OAUTH_CLIENT_ID`, `GITHUB_OAUTH_CLIENT_SECRET`, `GITHUB_OAUTH_REDIRECT_URI`, `GITHUB_APP_ID`, `GITHUB_APP_SLUG`, and `GITHUB_APP_PRIVATE_KEY_PATH`. The OAuth callback is `/github/oauth/callback`; the installation setup callback is `/github/app/installed`. Keep the downloaded private key outside the public web root, never commit it, and restrict its file permissions. The portal requests only `read:user` for identity and uses short-lived read-only installation tokens for selected repositories.
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
- Trigger deployments from a signed GitHub App push webhook; developers should not need to create or upload a workflow file or deployment token.
- Keep the deployment destination and cPanel credentials exclusively in the administrator-controlled workflow and secret store.
- Use short-lived, read-only GitHub App installation tokens in the admin workflow to fetch the registered private source repository; do not store per-developer source PATs in admin workflow secrets.
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

**Complete and deploy webhook-based project deployment.** Copy the prepared admin workflow into `saidmnuby/mitandao/.github/workflows/deploy.yml`, set the required GitHub Actions variables/secrets and portal `.env` values, enable the GitHub App Push webhook, deploy the portal code, and run the new migration on cPanel. Then push a commit to one approved developer repository and confirm the admin Actions run, callback result, and live destination. Keep cPanel credentials and all deployment tokens out of developer repositories. The local database table 500 fix and SQL backup feature also need deployment and a live check.
