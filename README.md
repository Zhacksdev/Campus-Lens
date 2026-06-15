# Service 1 - User Service
**Campus Lens | Tugas Besar IAE 2025/2026**

---

## Deskripsi Service

User Service bertanggung jawab atas seluruh manajemen akun dan profil mahasiswa. Service ini menjadi pusat identitas sistem. Setiap service lain bergantung pada `studentId` yang dikeluarkan service ini. Selain REST API, service ini juga mengekspose GraphQL via Hasura yang terhubung langsung ke PostgreSQL.

---

## Arsitektur & Teknologi

| Aspek | Detail |
|---|---|
| Framework | Node.js + Express |
| Database | PostgreSQL |
| API Protocol | RESTful API |
| GraphQL | Hasura |
| Port Service | `3001` |
| Port Hasura | `8080` |
| Message Broker | Tidak digunakan |

---

## Skema Database

```sql
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

CREATE TABLE students (
  id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name        VARCHAR(100) NOT NULL,
  email       VARCHAR(100) UNIQUE NOT NULL,
  password    VARCHAR(255) NOT NULL,
  major       VARCHAR(100) NOT NULL,
  semester    INT NOT NULL DEFAULT 1,
  career_goal VARCHAR(100),
  role        VARCHAR(20) DEFAULT 'student',
  created_at  TIMESTAMP DEFAULT NOW(),
  updated_at  TIMESTAMP DEFAULT NOW()
);

CREATE TABLE student_activities (
  id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  student_id  UUID REFERENCES students(id) ON DELETE CASCADE,
  type        VARCHAR(50) NOT NULL,
  name        VARCHAR(100) NOT NULL,
  description TEXT,
  date        DATE,
  created_at  TIMESTAMP DEFAULT NOW()
);
```

---

## Endpoints REST API

### Auth

| Method | Endpoint | Deskripsi |
|---|---|---|
| `POST` | `/api/auth/register` | Registrasi akun mahasiswa baru |
| `POST` | `/api/auth/login` | Login dan generate JWT |
| `POST` | `/api/auth/logout` | Invalidate token |

### Profile

| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/api/students/:id` | Ambil profil mahasiswa |
| `PUT` | `/api/students/:id` | Update profil mahasiswa |
| `GET` | `/api/students/:id/activities` | Daftar aktivitas mahasiswa |
| `POST` | `/api/students/:id/activities` | Tambah aktivitas mahasiswa |
| `DELETE` | `/api/students/:id/activities/:actId` | Hapus aktivitas mahasiswa |

### Internal

| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/internal/students/:id/profile` | Ambil profil untuk Recommendation Service |
| `GET` | `/internal/students?major=&semester=` | Filter mahasiswa untuk Recommendation Service |

---

## GraphQL via Hasura

Hasura terhubung ke PostgreSQL User Service dan dapat digunakan untuk operasi CRUD serta subscription.

```graphql
query GetStudent($id: uuid!) {
  students_by_pk(id: $id) {
    id
    name
    major
    semester
    career_goal
    student_activities {
      type
      name
      date
    }
  }
}

subscription WatchProfile($id: uuid!) {
  students_by_pk(id: $id) {
    semester
    career_goal
    updated_at
  }
}
```

Permission Hasura:

- Role `student`: hanya baca/edit data milik sendiri
- Role `admin`: full access

---

## Koneksi ke Service Lain

```text
User Service (Node.js)
|-- Hasura GraphQL :8080 -> API Gateway / Client
|-- REST Internal :3001 -> Recommendation Service
`-- REST Internal :3001 -> Career Roadmap Service
```

---

## Docker Setup

### Dockerfile

```dockerfile
FROM node:20-alpine
WORKDIR /app
COPY package*.json ./
RUN npm ci --only=production
COPY . .
EXPOSE 3001
CMD ["node", "src/index.js"]
```

### Environment

```env
PORT=3001
DATABASE_URL=postgresql://user:password@postgres-user:5432/userdb
JWT_SECRET=supersecretkey123
JWT_EXPIRES_IN=7d
NODE_ENV=development
```

### Docker Compose Snippet

```yaml
user-service:
  build: ./user-service
  ports:
    - "3001:3001"
  environment:
    DATABASE_URL: postgresql://user:password@postgres-user:5432/userdb
    JWT_SECRET: ${JWT_SECRET}
  depends_on:
    postgres-user:
      condition: service_healthy
  networks:
    - survival-hub-net

postgres-user:
  image: postgres:15-alpine
  environment:
    POSTGRES_DB: userdb
    POSTGRES_USER: user
    POSTGRES_PASSWORD: password
  volumes:
    - postgres_user_data:/var/lib/postgresql/data
    - ./user-service/init.sql:/docker-entrypoint-initdb.d/init.sql
  healthcheck:
    test: ["CMD-SHELL", "pg_isready -U user -d userdb"]
    interval: 5s
    retries: 5
  networks:
    - survival-hub-net

hasura:
  image: hasura/graphql-engine:v2.40.0
  ports:
    - "8080:8080"
  environment:
    HASURA_GRAPHQL_DATABASE_URL: postgresql://user:password@postgres-user:5432/userdb
    HASURA_GRAPHQL_ENABLE_CONSOLE: "true"
    HASURA_GRAPHQL_ADMIN_SECRET: ${HASURA_ADMIN_SECRET}
    HASURA_GRAPHQL_JWT_SECRET: '{"type":"HS256","key":"${JWT_SECRET}"}'
  depends_on:
    postgres-user:
      condition: service_healthy
  networks:
    - survival-hub-net
```

---

## Tutorial Setup Local

```bash
# 1. Install dependencies
npm install

# 2. Setup environment
cp .env.example .env

# 3. Jalankan PostgreSQL lokal atau Docker
docker run -d --name pg-user-dev \
  -e POSTGRES_DB=userdb \
  -e POSTGRES_USER=user \
  -e POSTGRES_PASSWORD=password \
  -p 5432:5432 \
  postgres:15-alpine

# 4. Jalankan migrasi
npm run migrate

# 5. Jalankan service
npm run dev
```

---

## Contoh Test Postman

### Health Check

```text
GET http://localhost:3001/health
```

### Register

```text
POST http://localhost:3001/api/auth/register
Content-Type: application/json
```

```json
{
  "name": "Budi",
  "email": "budi@uni.ac.id",
  "password": "secret123",
  "major": "Informatika",
  "semester": 3
}
```

### Login

```text
POST http://localhost:3001/api/auth/login
Content-Type: application/json
```

```json
{
  "email": "budi@uni.ac.id",
  "password": "secret123"
}
```

---

## Acceptance Criteria

| # | Kriteria | Status |
|---|---|---|
| AC-1 | `POST /api/auth/register` membuat akun baru dan return JWT | Todo |
| AC-2 | `POST /api/auth/login` return JWT valid dengan payload `{id, role, major, semester}` | Todo |
| AC-3 | Endpoint auth-protected menolak request tanpa token dengan status 401 | Todo |
| AC-4 | `GET /api/students/:id` return data profil lengkap | Todo |
| AC-5 | `PUT /api/students/:id` hanya bisa diakses pemilik akun sendiri | Todo |
| AC-6 | Hasura auto-generate GraphQL CRUD untuk tabel `students` | Todo |
| AC-7 | GraphQL subscription live-update saat profil berubah | Todo |
| AC-8 | Service berjalan di Docker container terpisah port 3001 | Todo |
| AC-9 | PostgreSQL berjalan di container terpisah dengan volume persisten | Todo |
| AC-10 | `/internal/students/:id/profile` dapat diakses Recommendation Service | Todo |

---

## Struktur Folder

```text
user-service/
|-- src/
|   |-- controllers/
|   |   |-- authController.js
|   |   `-- studentController.js
|   |-- middleware/
|   |   `-- auth.js
|   |-- models/
|   |   `-- student.js
|   |-- routes/
|   |   |-- authRoutes.js
|   |   |-- studentRoutes.js
|   |   `-- internalRoutes.js
|   `-- index.js
|-- init.sql
|-- Dockerfile
|-- .env.example
|-- package.json
`-- README.md
```

---

## Links

- GitHub Repo: `https://github.com/Zhacksdev/Campus-Lens`
- Hasura Console: `http://localhost:8080`
