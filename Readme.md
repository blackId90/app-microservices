# Microservices (Laravel) + Kong Gateway + PostgreSQL + Redis + Pgbouncer

## Deskripsi Singkat Project
Project ini membangun arsitektur **microservices** berbasis **Laravel** yang dijalankan menggunakan **Docker Compose**. **Kong Gateway** berperan sebagai *API Gateway / Reverse Proxy* untuk mengatur akses eksternal ke seluruh service.

## Teknologi Stack
| Component | Technology |
|-----------|------------|
| **Framework** | Laravel 13 |
| **PHP Version** | PHP 8.5 |
| **Database** | PostgreSQL 16 |
| **Cache** | Redis |
| **API Gateway** | Kong OSS 3.9.1 |
| **Connection Pooling** | PgBouncer |
| **Architecture** | Microservice (Model-Repository-Service-Controller) |
| **Container** | Docker |

## Infrastruktur / Arsitektur Sistem
Alur trafik dan dependency tingkat tinggi:

```
                (1) Client Request
                        |
                        ▼
                 +--------------+
                 |  Kong        |
                 |  Gateway     |
                 +--------------+
                        |
                        | (2) HTTP(S) routing -----------------+
                        ▼                                      |
   +--------------------+-----------------+                    |
   |                                      |                    |
   ▼                                      ▼                    |
+---------+                        +-------------+             |
| Auth    |                        | Control     |             |
| Service |                        | Center      |             |
+---------+                        | Service     |             |
     |                             +-------------+             |
     |                                    |                    |
     |                                    |                    |
     |<-----------------------------------+                    |
     |                                                         ▼
     |                                                   +-------------+
     | (3) DB/Redis access <---------------------------->|             |
     |                                                   |    Redis    |
     |                                                   |             |
     |                                                   +-------------+
     |                                              (cache/queue/rate limiter)
     ▼
+------------------+        +------------------+        
| Pgbouncer        |<------>| PostgreSQL       |
| (pooling conn)   |        | (volume data)    |        
+------------------+        +------------------+
```

1. **Client** mengakses endpoint publik melalui **Kong Gateway**.
2. **Kong** meneruskan request ke service internal yang sesuai (Auth/Control Center) pada jaringan Docker `network_local`.
3. Service Laravel berinteraksi dengan data melalui:
   - **PostgreSQL** (umumnya melalui **Pgbouncer** untuk pooling koneksi)
   - **Redis** (jika fitur cache/queue diaktifkan)
4. Tiap service menggunakan konfigurasi environment sendiri melalui `*.env.local`.

## Komunikasi Antar Service
```
┌──────────────┐   (HTTP/HTTPS)   ┌──────────────────────┐
│     Client   │ ───────────────▶ │     Kong Gateway     │
└──────────────┘                  └────────────┬─────────┘
                                               │ routing
                                               ▼
                                   ┌───────────────────────────┐
                                   │   Service Laravel (API)   │
                                   └─────────┬─────────────────┘
                                             │
                                             │ (HTTP internal request)
                                             ▼
                                   ┌───────────────────────────┐
                                   │    Auth Service (Internal)│
                                   └───────────────────────────┘
```

Keterangan autentikasi/otorisasi yang diterapkan saat request melewati service:
- **JWT token validation**: validasi *bearer token* (JWT) untuk memastikan user/claims masih valid.
- **API Key**: klien mengirim header **`X-Api-Key`** untuk verifikasi identitas consumer/API.

> Catatan: untuk detail route/endpoint spesifik, lihat `routes/api.php` di masing-masing service dan konfigurasi Kong di folder `kong/`.

## Deskripsi Masing-masing Service
### 1) Kong Gateway (`kong/`)
- Single entry point untuk akses API
- Routing request ke service internal
- Endpoint admin untuk konfigurasi/monitoring

### 2) Auth Service (`auth-service/`)
- Domain: autentikasi & authorization
- Menyediakan API melalui routing Laravel
- Mengelola skema database dengan:
  - migrations
  - seeding (`php artisan migrate:fresh --seed`)

### 3) Control Center Service (`control-center-service/`)
- Domain: control center (sesuai implementasi project)
- Menyediakan API via Laravel routes
- Mengelola skema database melalui migrations & seeding

### 4) PostgreSQL (`postgresql/`)
- Database utama
- Data disimpan ke volume `storage-postgres-local`

### 5) Pgbouncer (`pgbouncer/`)
- Connection pooling menuju PostgreSQL
- Mengurangi overhead dan latensi pembuatan koneksi

### 6) Redis (`redis/`)
- Cache/queue storage
- Data/persist disimpan ke volume `storage-redis-local`

---

## Prasyarat
- Docker / Docker Compose sudah terpasang.
- Semua service memakai file environment `*.env.local` masing-masing folder:
  - `./kong/.env.local`
  - `./auth-service/.env.local`
  - `./control-center-service/.env.local`
  - `./pgbouncer/.env.local`
  - `./postgresql/.env.local`

## Build & Jalankan (Local)
Jalankan seluruh service (Kong, Auth Service, Control Center Service, PostgreSQL, Pgbouncer, Redis):

```bash
docker compose -f docker-compose.local.yml up --build -d
```

## Setup Database (Migration + Seed)
Karena database diisi melalui command Laravel, lakukan migrate dan seeding untuk masing-masing service.

### Auth Service
```bash
docker exec -it auth-service bash
php artisan migrate:fresh --seed
```

### Control Center Service
```bash
docker exec -it control-center-service bash
php artisan migrate:fresh --seed
```

## Cara Akses
### Kong Gateway (Public)
- http://localhost:8000

### Kong Admin API
- http://localhost:8081

## Notes Penting (Port)
Pastikan port berikut tidak bentrok di komputer Anda:
- `8000` (Kong public)
- `8081` (Kong admin)
- `5432` (PostgreSQL)
- `6432` (Pgbouncer)
- `6379` (Redis)

## Troubleshooting
- Lihat log container:
```bash
docker compose -f docker-compose.local.yml logs -f
```

- Jika migrasi gagal, pastikan database sudah ready dan konfigurasi `.env.local` masing-masing service sesuai (terutama host/port DB dan kredensial).
