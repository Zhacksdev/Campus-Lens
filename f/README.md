# 🤖 Service 4 — Recommendation Service
**Campus Lens | Tugas Besar IAE 2025/2026**

---

## 📋 Deskripsi Service

Recommendation Service adalah inti dari Student Survival Hub. Service ini memberikan **rekomendasi personal** kepada mahasiswa berdasarkan profil mereka (program studi, semester, target karier, aktivitas). Service ini adalah **consumer utama** event-driven architecture — ia mendengarkan event dari Course Review Service, memperbarui rekomendasi, lalu **mempublish event** `RecommendationUpdated` ke Notification layer. Selain REST, service ini juga mengekspose **GraphQL manual menggunakan Apollo Server** (Node.js).

---

## ⚙️ Arsitektur & Teknologi

| Aspek | Detail |
|---|---|
| **Framework** | Node.js + Express + Apollo Server |
| **Database** | PostgreSQL (dedicated container) |
| **API Protocol** | RESTful API + **GraphQL manual (Apollo Server)** |
| **Message Broker** | RabbitMQ — **consume** event + **publish** event |
| **Port REST** | `8000` |
| **Port GraphQL** | `4000` |
| **Container** | Docker (image sendiri) |

### Kenapa Node.js + Apollo Server?
Apollo Server adalah library GraphQL paling populer untuk Node.js dengan ecosystem yang mature. Schema dan resolver dibuat manual sesuai kebutuhan bisnis (bukan auto-generated), memenuhi ketentuan GraphQL backend manual di tugas ini.

---

## 🗄️ Skema Database

```sql
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

CREATE TABLE recommendations (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  student_id      VARCHAR(50) UNIQUE NOT NULL,
  suggestions     TEXT[] NOT NULL,
  target_career   VARCHAR(100),
  score_breakdown JSONB,
  updated_at      TIMESTAMP DEFAULT NOW(),
  created_at      TIMESTAMP DEFAULT NOW()
);

CREATE TABLE recommendation_history (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  student_id      VARCHAR(50) NOT NULL,
  suggestions     TEXT[] NOT NULL,
  trigger_event   VARCHAR(100),
  created_at      TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_rec_student ON recommendations(student_id);
CREATE INDEX idx_rh_student ON recommendation_history(student_id);
```

---

## 🔌 Endpoints REST API

| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/api/recommendations/:studentId` | Ambil rekomendasi aktif mahasiswa |
| `POST` | `/api/recommendations/generate` | Generate rekomendasi baru (trigger manual) |
| `GET` | `/api/recommendations/:studentId/history` | Riwayat perubahan rekomendasi |

### Internal
| Method | Endpoint | Deskripsi |
|---|---|---|
| `POST` | `/internal/recommendations/refresh` | Trigger refresh dari service lain |

---

## 📡 GraphQL Schema (Apollo Server — Manual)

GraphQL server berjalan di port `4000` endpoint `/graphql`.

### Schema (`src/graphql/typeDefs.js`)
```graphql
type Query {
  recommendationsByStudent(studentId: ID!): Recommendation
  recommendationHistory(studentId: ID!, limit: Int): [RecommendationRecord!]!
}

type Recommendation {
  id:           ID!
  studentId:    ID!
  suggestions:  [String!]!
  targetCareer: String
  updatedAt:    String!
}

type RecommendationRecord {
  id:           ID!
  suggestions:  [String!]!
  triggerEvent: String
  createdAt:    String!
}

type Mutation {
  updateRecommendation(studentId: ID!, suggestions: [String!]!): Recommendation!
}
```

### Resolvers (`src/graphql/resolvers.js`)
```javascript
const resolvers = {
  Query: {
    recommendationsByStudent: async (_, { studentId }, { db }) => {
      const result = await db.query(
        'SELECT * FROM recommendations WHERE student_id = $1',
        [studentId]
      );
      return result.rows[0] || null;
    },
    recommendationHistory: async (_, { studentId, limit = 10 }, { db }) => {
      const result = await db.query(
        'SELECT * FROM recommendation_history WHERE student_id = $1 ORDER BY created_at DESC LIMIT $2',
        [studentId, limit]
      );
      return result.rows;
    }
  },
  Mutation: {
    updateRecommendation: async (_, { studentId, suggestions }, { db }) => {
      const result = await db.query(
        `INSERT INTO recommendations (student_id, suggestions)
         VALUES ($1, $2)
         ON CONFLICT (student_id) DO UPDATE SET suggestions = $2, updated_at = NOW()
         RETURNING *`,
        [studentId, suggestions]
      );
      return result.rows[0];
    }
  }
};
```

### Contoh Query
```graphql
# Ambil rekomendasi aktif
query {
  recommendationsByStudent(studentId: "uuid-mahasiswa") {
    suggestions
    targetCareer
    updatedAt
  }
}

# Riwayat 5 terakhir (partial query)
query {
  recommendationHistory(studentId: "uuid-mahasiswa", limit: 5) {
    suggestions
    createdAt
  }
}

# Update via mutation
mutation {
  updateRecommendation(
    studentId: "uuid-mahasiswa"
    suggestions: ["Ikuti himpunan sebelum semester 4", "Coba sertifikasi AWS semester 5"]
  ) {
    id
    updatedAt
  }
}
```

---

## 📨 Events yang Di-consume & Di-publish

### Events yang Di-CONSUME
| Event | Source | Aksi |
|---|---|---|
| `ReviewSubmitted` | Course Review Service | Perbarui bobot rekomendasi MK terkait |
| `DifficultyScoreUpdated` | Course Review Service | Sinkronisasi bobot MK berdasarkan difficulty terbaru |

### Event yang Di-PUBLISH
#### `RecommendationUpdated`
```json
{
  "event": "RecommendationUpdated",
  "timestamp": "2025-06-09T10:05:00Z",
  "data": {
    "student_id": "uuid-mahasiswa",
    "new_suggestions": [
      "Daftar UKM Robotika sebelum semester 4",
      "Ambil AWS Cloud Practitioner di semester 5"
    ],
    "trigger_event": "ReviewSubmitted",
    "target_career": "Backend Engineer"
  }
}
```
**Consumer:** Notification Service (out of scope, event tetap dipublish untuk simulasi)

---

## 🔗 Koneksi ke Service Lain

```
Recommendation Service (Node.js)
  │
  ├── [RabbitMQ CONSUME] ←── Course Review Service
  │     event: ReviewSubmitted, DifficultyScoreUpdated
  │
  ├── [RabbitMQ PUBLISH] ──→ (Notification Service / log)
  │     event: RecommendationUpdated
  │
  ├── [Apollo GraphQL :4000] ←── API Gateway / Client
  │
  ├── [REST Internal] ──→ User Service :3001
  │     GET /internal/students/:id/profile
  │
  └── [REST Internal] ──→ Career Roadmap Service :3003
        GET /internal/roadmap/phases
```

---

## 🐳 Docker Setup

### `Dockerfile`
```dockerfile
FROM node:20-alpine
WORKDIR /app
COPY package*.json ./
RUN npm ci --only=production
COPY . .
EXPOSE 8000
EXPOSE 4000
CMD ["node", "src/index.js"]
```

### `.env`
```env
REST_PORT=8000
GRAPHQL_PORT=4000
DATABASE_URL=postgresql://user:password@postgres-rec:5432/recdb
RABBITMQ_URL=amqp://guest:guest@rabbitmq:5672/
RABBITMQ_EXCHANGE=student-survival-hub
USER_SERVICE_URL=http://user-service:3001
CAREER_SERVICE_URL=http://career-roadmap-service:3003
NODE_ENV=development
```

### Docker Compose Snippet
```yaml
recommendation-service:
  build: ./recommendation-service
  ports:
    - "8000:8000"
    - "4000:4000"
  environment:
    DATABASE_URL: postgresql://user:password@postgres-rec:5432/recdb
    RABBITMQ_URL: amqp://guest:guest@rabbitmq:5672/
    USER_SERVICE_URL: http://user-service:3001
    CAREER_SERVICE_URL: http://career-roadmap-service:3003
  depends_on:
    postgres-rec:
      condition: service_healthy
    rabbitmq:
      condition: service_healthy
  networks: [survival-hub-net]

postgres-rec:
  image: postgres:15-alpine
  environment:
    POSTGRES_DB: recdb
    POSTGRES_USER: user
    POSTGRES_PASSWORD: password
  volumes:
    - postgres_rec_data:/var/lib/postgresql/data
    - ./recommendation-service/init.sql:/docker-entrypoint-initdb.d/init.sql
  healthcheck:
    test: ["CMD-SHELL", "pg_isready -U user -d recdb"]
    interval: 5s
    retries: 5
  networks: [survival-hub-net]
```

---

## 🚀 Tutorial Setup (Local Dev)

```bash
# 1. Clone repo
git clone https://github.com/<org>/recommendation-service.git && cd recommendation-service

# 2. Install dependencies
npm install

# 3. Setup environment
cp .env.example .env

# 4. Jalankan PostgreSQL + RabbitMQ
docker run -d --name pg-rec-dev \
  -e POSTGRES_DB=recdb -e POSTGRES_USER=user -e POSTGRES_PASSWORD=password \
  -p 5435:5432 postgres:15-alpine

docker run -d --name rabbitmq-dev \
  -p 5672:5672 -p 15672:15672 rabbitmq:3-management

# 5. Jalankan migrasi
npm run migrate

# 6. Jalankan service
npm run dev
# REST API: http://localhost:8000
# GraphQL:  http://localhost:4000/graphql

# 7. Test generate rekomendasi
curl -X POST http://localhost:8000/api/recommendations/generate \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <JWT>" \
  -d '{"student_id": "uuid-mahasiswa"}'

# 8. Simulasi consume event (dari terminal lain)
node scripts/testPublishEvent.js ReviewSubmitted
# Cek apakah rekomendasi mahasiswa ter-update
```

Buka Apollo Sandbox di `http://localhost:4000/graphql` untuk test GraphQL.

---

## ✅ Acceptance Criteria

| # | Kriteria | Status |
|---|---|---|
| AC-1 | `GET /api/recommendations/:studentId` return rekomendasi aktif | ☐ |
| AC-2 | `POST /api/recommendations/generate` menghasilkan rekomendasi berdasarkan profil | ☐ |
| AC-3 | GraphQL query `recommendationsByStudent` berjalan dan return data | ☐ |
| AC-4 | GraphQL partial query (hanya `suggestions` tanpa `targetCareer`) berjalan | ☐ |
| AC-5 | GraphQL mutation `updateRecommendation` berhasil memperbarui data | ☐ |
| AC-6 | Service **consume** event `ReviewSubmitted` dari RabbitMQ | ☐ |
| AC-7 | Service **consume** event `DifficultyScoreUpdated` dan sinkronisasi bobot | ☐ |
| AC-8 | Setelah rekomendasi diperbarui, event `RecommendationUpdated` ter-publish | ☐ |
| AC-9 | Service berhasil memanggil User Service + Career Service via REST internal | ☐ |
| AC-10 | Consumer RabbitMQ aktif otomatis saat container berjalan | ☐ |

---

## 📝 To-Do List

### Setup
- [ ] Init project Node.js
- [ ] Install `@apollo/server`, `graphql`, `pg`, `amqplib`, `axios`, `dotenv`
- [ ] Struktur folder
- [ ] Buat `Dockerfile` dan `.env.example`

### Database
- [ ] Buat `init.sql` (tabel `recommendations` + `recommendation_history`)
- [ ] Setup koneksi PostgreSQL dengan `pg`
- [ ] Script `npm run migrate`

### REST API
- [ ] `GET /api/recommendations/:studentId`
- [ ] `POST /api/recommendations/generate`
- [ ] `GET /api/recommendations/:studentId/history`
- [ ] Endpoint internal `/internal/recommendations/refresh`

### GraphQL (Apollo Server Manual)
- [ ] Definisikan `typeDefs` sesuai schema di atas
- [ ] Implementasi resolver `Query.recommendationsByStudent`
- [ ] Implementasi resolver `Query.recommendationHistory`
- [ ] Implementasi resolver `Mutation.updateRecommendation`
- [ ] Setup Apollo Server di port 4000
- [ ] Test semua query via Apollo Sandbox

### Message Broker (RabbitMQ)
- [ ] Setup koneksi `amqplib` ke RabbitMQ
- [ ] Buat `consumer.js` — subscribe ke exchange `student-survival-hub`
- [ ] Handler event `ReviewSubmitted`
- [ ] Handler event `DifficultyScoreUpdated`
- [ ] Logika kalkulasi/update skor rekomendasi
- [ ] Publish event `RecommendationUpdated` setelah update
- [ ] Jalankan consumer sebagai background process saat startup

### Integrasi Service Lain
- [ ] HTTP client untuk call User Service (GET profil)
- [ ] HTTP client untuk call Career Roadmap Service (GET phases)
- [ ] Error handling jika service lain timeout/down

### Dokumentasi
- [ ] Script `testPublishEvent.js` untuk simulasi event manual
- [ ] Postman Collection REST + GraphQL
- [ ] Test semua Acceptance Criteria

---

## 📁 Struktur Folder

```
recommendation-service/
├── src/
│   ├── graphql/
│   │   ├── typeDefs.js
│   │   └── resolvers.js
│   ├── rest/
│   │   ├── routes.js
│   │   └── controllers/
│   │       └── recommendationController.js
│   ├── messaging/
│   │   ├── consumer.js
│   │   └── publisher.js
│   ├── services/
│   │   ├── recommendationService.js
│   │   └── httpClient.js
│   ├── db/
│   │   └── index.js
│   └── index.js
├── scripts/
│   └── testPublishEvent.js
├── init.sql
├── Dockerfile
├── .env.example
├── package.json
└── README.md
```

---

## 🔗 Links

- **GitHub Repo:** `https://github.com/<org>/recommendation-service`
- **Postman Collection:** `<link>`
- **GraphQL Playground:** `http://localhost:4000/graphql`
