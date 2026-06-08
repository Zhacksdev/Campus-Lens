# 🗺️ Service 3 — Career Roadmap Service
**Student Survival Hub | Tugas Besar IAE 2025/2026**

---

## 📋 Deskripsi Service

Career Roadmap Service mengelola jalur pengembangan diri mahasiswa berdasarkan target karier. Service ini menyajikan roadmap per semester (1–7), rekomendasi sertifikasi, dan persiapan magang terstruktur. Service ini mengimplementasikan **GraphQL secara manual menggunakan Lighthouse** (library GraphQL untuk Laravel) — sehingga query dapat dilakukan secara fleksibel sesuai kebutuhan klien.

---

## ⚙️ Arsitektur & Teknologi

| Aspek | Detail |
|---|---|
| **Framework** | Laravel 11 (PHP 8.2) |
| **Database** | MySQL 8.0 (dedicated container) |
| **API Protocol** | RESTful API + **GraphQL manual (Lighthouse)** |
| **Message Broker** | — (tidak menggunakan MQ) |
| **Port REST** | `3003` |
| **Port GraphQL** | `3003/graphql` (satu port, beda endpoint) |
| **GraphQL** | Lighthouse (manual schema + resolver) |

### Kenapa Laravel + Lighthouse?
Lighthouse adalah library GraphQL resmi untuk Laravel yang memungkinkan definisi schema GraphQL dengan sintaks SDL (Schema Definition Language) dan resolver langsung terhubung ke Eloquent model. Ini memenuhi syarat **GraphQL backend manual** sesuai ketentuan tugas karena skema dan resolver dibuat sendiri — bukan auto-generated.

---

## 🗄️ Skema Database

```sql
CREATE TABLE career_paths (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  major       VARCHAR(100) NOT NULL,
  description TEXT,
  created_at  TIMESTAMP DEFAULT NOW(),
  updated_at  TIMESTAMP DEFAULT NOW()
);

CREATE TABLE roadmap_phases (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  career_path_id  INT NOT NULL,
  semester        INT NOT NULL,
  focus           VARCHAR(200) NOT NULL,
  created_at      TIMESTAMP DEFAULT NOW(),
  FOREIGN KEY (career_path_id) REFERENCES career_paths(id) ON DELETE CASCADE
);

CREATE TABLE phase_activities (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  phase_id    INT NOT NULL,
  type        VARCHAR(50) NOT NULL,
  title       VARCHAR(200) NOT NULL,
  description TEXT,
  priority    INT DEFAULT 1,
  FOREIGN KEY (phase_id) REFERENCES roadmap_phases(id) ON DELETE CASCADE
);

CREATE TABLE certifications (
  id                      INT AUTO_INCREMENT PRIMARY KEY,
  career_path_id          INT,
  name                    VARCHAR(200) NOT NULL,
  provider                VARCHAR(100),
  recommended_semester    INT,
  url                     VARCHAR(300),
  FOREIGN KEY (career_path_id) REFERENCES career_paths(id)
);
```

---

## 🔌 Endpoints REST API

| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/api/careers` | Daftar semua jalur karier |
| `GET` | `/api/careers/:id` | Detail satu jalur karier |
| `GET` | `/api/roadmap?major=&semester=` | Roadmap berdasarkan major & semester |
| `GET` | `/api/certifications?career=` | Sertifikasi yang direkomendasikan |
| `POST` | `/api/careers` | Tambah career path (admin) |
| `POST` | `/api/careers/:id/phases` | Tambah fase ke career path (admin) |

### Internal
| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/internal/roadmap/phases?major=&semester=&career=` | Data fase untuk Recommendation Service |

---

## 📡 GraphQL Schema (Lighthouse — Manual)

GraphQL tersedia di endpoint `POST /graphql`.

### Schema (`graphql/schema.graphql`)
```graphql
type Query {
  careerRoadmap(major: String!, careerGoal: String!): CareerRoadmap
  careerPaths(major: String): [CareerPath!]!
  roadmapPhase(major: String!, semester: Int!, careerGoal: String!): Phase
  certifications(careerGoal: String!, minSemester: Int, maxSemester: Int): [Certification!]!
}

type CareerRoadmap {
  careerPath:     CareerPath!
  phases:         [Phase!]!
  totalSemesters: Int!
}

type CareerPath {
  id:          ID!
  name:        String!
  major:       String!
  description: String
}

type Phase {
  semester:   Int!
  focus:      String!
  activities: [Activity!]!
}

type Activity {
  type:        String!
  title:       String!
  description: String
  priority:    Int!
}

type Certification {
  name:                String!
  provider:            String
  recommendedSemester: Int
  url:                 String
}

type Mutation {
  addCareerPath(name: String!, major: String!, description: String): CareerPath!
    @can(ability: "admin")
  addPhase(careerPathId: ID!, semester: Int!, focus: String!): Phase!
    @can(ability: "admin")
}
```

### Contoh Query GraphQL
```graphql
# Ambil roadmap Backend Engineer untuk Informatika
query GetRoadmap {
  careerRoadmap(major: "Informatika", careerGoal: "Backend Engineer") {
    careerPath { name description }
    phases {
      semester
      focus
      activities { type title priority }
    }
  }
}

# Partial query — hanya ambil focus (efisiensi GraphQL)
query GetFocus {
  roadmapPhase(major: "Informatika", semester: 3, careerGoal: "Backend Engineer") {
    focus
  }
}

# Sertifikasi semester 3–5
query GetCerts {
  certifications(careerGoal: "Data Scientist", minSemester: 3, maxSemester: 5) {
    name
    provider
    recommendedSemester
    url
  }
}
```

---

## 🔗 Koneksi ke Service Lain

```
Career Roadmap Service (Laravel)
  │
  ├── [GraphQL Lighthouse :3003/graphql] ←── API Gateway / Client
  │
  ├── [REST Internal :3003] ←── Recommendation Service
  │     GET /internal/roadmap/phases
  │
  └── [REST call] ──→ User Service (validasi JWT)
        GET /internal/students/:id/profile
```

---

## 🐳 Docker Setup

### `Dockerfile`
```dockerfile
FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx supervisor \
    && docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && php artisan config:cache \
    && php artisan route:cache

EXPOSE 3003
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=3003"]
```

### `.env`
```env
APP_PORT=3003
DB_CONNECTION=mysql
DB_HOST=mysql-career
DB_PORT=3306
DB_DATABASE=careerdb
DB_USERNAME=user
DB_PASSWORD=password

JWT_SECRET=supersecretkey123
USER_SERVICE_URL=http://user-service:3001
```

### Docker Compose Snippet
```yaml
career-roadmap-service:
  build: ./career-roadmap-service
  ports: ["3003:3003"]
  environment:
    DB_HOST: mysql-career
    DB_DATABASE: careerdb
    JWT_SECRET: ${JWT_SECRET}
    USER_SERVICE_URL: http://user-service:3001
  depends_on:
    mysql-career:
      condition: service_healthy
  networks: [survival-hub-net]

mysql-career:
  image: mysql:8.0
  environment:
    MYSQL_DATABASE: careerdb
    MYSQL_USER: user
    MYSQL_PASSWORD: password
    MYSQL_ROOT_PASSWORD: rootpassword
  volumes:
    - mysql_career_data:/var/lib/mysql
  healthcheck:
    test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "user", "-ppassword"]
    interval: 5s
    retries: 10
  networks: [survival-hub-net]
```

---

## 🚀 Tutorial Setup (Local Dev)

```bash
# 1. Clone repo
git clone https://github.com/<org>/career-roadmap-service.git && cd career-roadmap-service

# 2. Install dependencies (termasuk Lighthouse)
composer install
# Pastikan nuwave/lighthouse ada di composer.json

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Jalankan MySQL
docker run -d --name mysql-career-dev \
  -e MYSQL_DATABASE=careerdb -e MYSQL_USER=user \
  -e MYSQL_PASSWORD=password -e MYSQL_ROOT_PASSWORD=root \
  -p 3307:3306 mysql:8.0

# 5. Migrasi + seed data roadmap
php artisan migrate
php artisan db:seed --class=CareerSeeder

# 6. Jalankan service
php artisan serve --port=3003

# 7. Test REST
curl http://localhost:3003/api/roadmap?major=Informatika&semester=3

# 8. Test GraphQL (via curl)
curl -X POST http://localhost:3003/graphql \
  -H "Content-Type: application/json" \
  -d '{"query":"{ careerPaths(major: \"Informatika\") { id name } }"}'
```

---

## ✅ Acceptance Criteria

| # | Kriteria | Status |
|---|---|---|
| AC-1 | `GET /api/roadmap?major=&semester=` return data roadmap relevan | ☐ |
| AC-2 | GraphQL query `careerRoadmap(major, careerGoal)` return data lengkap | ☐ |
| AC-3 | GraphQL partial query (hanya field `focus` tanpa `activities`) berjalan | ☐ |
| AC-4 | GraphQL mutation `addCareerPath` berhasil menambah data | ☐ |
| AC-5 | GraphQL tersedia di endpoint `/graphql` dan bisa diakses via Postman/curl | ☐ |
| AC-6 | Skema GraphQL terdokumentasi + introspection aktif di development | ☐ |
| AC-7 | Service REST berjalan di Docker container port 3003 | ☐ |
| AC-8 | Data roadmap ter-seed saat container pertama kali berjalan | ☐ |
| AC-9 | Endpoint internal `/internal/roadmap/phases` bisa diakses Recommendation Service | ☐ |
| AC-10 | Resolver GraphQL tidak over-fetching (hanya query field yang diminta) | ☐ |

---

## 📝 To-Do List

### Setup
- [ ] Init project Laravel 11
- [ ] Install Lighthouse: `composer require nuwave/lighthouse`
- [ ] Publish config Lighthouse: `php artisan vendor:publish --tag=lighthouse-config`
- [ ] Buat `Dockerfile` dan `.env.example`

### Database
- [ ] Migration tabel `career_paths`, `roadmap_phases`, `phase_activities`, `certifications`
- [ ] Seeder: minimal 5 career paths dengan 4–7 fase masing-masing
- [ ] Eloquent Model + relasi

### REST API
- [ ] CRUD `/api/careers` dan `/api/roadmap`
- [ ] CRUD `/api/certifications`
- [ ] Endpoint admin (POST) dengan auth guard
- [ ] Endpoint internal `/internal/roadmap/phases`

### GraphQL (Lighthouse)
- [ ] Definisikan schema di `graphql/schema.graphql`
- [ ] Buat resolver `CareerRoadmapQuery.php`
- [ ] Buat resolver `RoadmapPhaseQuery.php`
- [ ] Buat resolver `CertificationsQuery.php`
- [ ] Buat resolver mutation `AddCareerPathMutation.php`
- [ ] Test semua query dan mutation via Postman/GraphiQL

### Dokumentasi
- [ ] Postman Collection REST + GraphQL
- [ ] Dokumentasi semua query GraphQL di README
- [ ] Test semua Acceptance Criteria

---

## 📁 Struktur Folder

```
career-roadmap-service/
├── app/
│   ├── GraphQL/
│   │   ├── Queries/
│   │   │   ├── CareerRoadmapQuery.php
│   │   │   ├── RoadmapPhaseQuery.php
│   │   │   └── CertificationsQuery.php
│   │   └── Mutations/
│   │       └── AddCareerPathMutation.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── CareerController.php
│   │   │   └── RoadmapController.php
│   │   └── Middleware/
│   │       └── JwtMiddleware.php
│   └── Models/
│       ├── CareerPath.php
│       ├── RoadmapPhase.php
│       ├── PhaseActivity.php
│       └── Certification.php
├── graphql/
│   └── schema.graphql
├── database/
│   ├── migrations/
│   └── seeders/
│       └── CareerSeeder.php
├── routes/api.php
├── Dockerfile
├── .env.example
├── composer.json
└── README.md
```

---

## 🔗 Links

- **GitHub Repo:** `https://github.com/<org>/career-roadmap-service`
- **Postman Collection:** `<link>`
- **GraphQL Endpoint:** `http://localhost:3003/graphql`
