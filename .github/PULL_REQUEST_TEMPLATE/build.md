## 📦 Build: [Title]
Changes related to the build system, Docker infrastructure, asset compilation, or deployment artifacts for Laravel 13.

### 📝 Description
This PR involves updates to the containerization strategy, build pipeline, or asset management specifically tailored for Laravel 13 and PHP 8.4+ environments.

### 🛠️ Infrastructure & Build Changes
- Updates to `Dockerfile`, `docker-compose.yml`, or container runtime configurations.
- Adjustments to `composer.json` or core framework configurations unique to L13.
- Recompiled production assets and ensured synchronization within the container volumes.
- Modified `.env.example` or build-time environment variables.

### ✅ Verification Results
- [ ] `docker-compose up --build` executed successfully without errors.
- [ ] Verified Laravel 13 environment via `php artisan --version`.
- [ ] `npm run build` completed inside the application container.
- [ ] All services (App, DB, Cache) passed Docker health checks.
- [ ] Vite manifest file is correctly mapped to the host/container.
- [ ] Documentation has been updated (if applicable).

### ⚠️ Deployment Note
- [ ] Requires a full **image rebuild** (`docker-compose build`) on the production server.
- [ ] Migration required: `php artisan migrate`.
- [ ] Application builds without errors (`php artisan optimize:clear`).
- [ ] All automated tests passed (`php artisan test`) on local environment.
- [ ] Update CI/CD environment secrets to match new Docker/L13 requirements.
