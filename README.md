# 📚 Service 2 — Course Review Service
**Campus Lens | Tugas Besar IAE 2025/2026**

---

## 📋 Deskripsi Service

Course Review Service mengelola seluruh ulasan mata kuliah dan dosen. Mahasiswa dapat melihat tingkat kesulitan mata kuliah (skala 1–10), gaya mengajar dosen, tips belajar, strategi UTS/UAS, dan review dari senior. Service ini menjadi **publisher event** ke RabbitMQ setiap kali review baru dikirimkan, sehingga Recommendation Service dapat menyinkronisasi bobotnya secara real-time.

---

## ⚙️ Arsitektur & Teknologi

| Aspek | Detail |
|---|---|
| **Framework** | Laravel 11 (PHP 8.2) |
| **Database** | MySQL 8.0 (dedicated container) |
| **API Protocol** | RESTful API |
| **Message Broker** | RabbitMQ — **publish** event `ReviewSubmitted` & `DifficultyScoreUpdated` |
| **Port Service** | `3002` |
| **GraphQL** | — (tidak digunakan di service ini) |

### Kenapa Laravel + MySQL?
Data ulasan bersifat relasional (kursus → dosen → review → mahasiswa). Laravel menyediakan Eloquent ORM untuk relasi kompleks, dan queue system bawaan yang bisa di-driver ke RabbitMQ untuk publish event secara async.

---

## 🗄️ Skema Database

```sql
CREATE TABLE courses (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  code        VARCHAR(20) UNIQUE NOT NULL,
  name        VARCHAR(150) NOT NULL,
  credits     INT NOT NULL,
  major       VARCHAR(100) NOT NULL,
  semester    INT NOT NULL,
  created_at  TIMESTAMP DEFAULT NOW(),
  updated_at  TIMESTAMP DEFAULT NOW()
);

CREATE TABLE lecturers (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  name            VARCHAR(100) NOT NULL,
  email           VARCHAR(100),
  teaching_style  VARCHAR(50),
  created_at      TIMESTAMP DEFAULT NOW()
);

CREATE TABLE course_reviews (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  course_id       INT NOT NULL,
  lecturer_id     INT,
  student_id      VARCHAR(50) NOT NULL,
  difficulty      INT NOT NULL,
  teaching_rating INT NOT NULL,
  tips            TEXT,
  uts_strategy    TEXT,
  uas_strategy    TEXT,
  semester_taken  INT,
  academic_year   VARCHAR(10),
  created_at      TIMESTAMP DEFAULT NOW(),
  updated_at      TIMESTAMP DEFAULT NOW(),
  FOREIGN KEY (course_id) REFERENCES courses(id),
  FOREIGN KEY (lecturer_id) REFERENCES lecturers(id)
);
```

---

## 🔌 Endpoints REST API

### Courses
| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/api/courses` | Daftar mata kuliah (filter: `?major=&semester=`) |
| `GET` | `/api/courses/:id` | Detail MK + avg difficulty |
| `GET` | `/api/courses/:id/reviews` | Semua review untuk MK |
| `POST` | `/api/courses` | Tambah MK (admin) |

### Reviews
| Method | Endpoint | Deskripsi |
|---|---|---|
| `POST` | `/api/reviews` | Submit review baru **(trigger publish event)** |
| `GET` | `/api/reviews/:id` | Detail satu review |
| `PUT` | `/api/reviews/:id` | Edit review milik sendiri |
| `DELETE` | `/api/reviews/:id` | Hapus review milik sendiri |

### Lecturers
| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/api/lecturers` | Daftar dosen |
| `GET` | `/api/lecturers/:id` | Profil dosen + MK yang diajar |

### Internal
| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/internal/courses/difficulty?major=&semester=` | Data difficulty untuk Recommendation Service |

---

## 📨 Events yang Di-publish ke RabbitMQ

### `ReviewSubmitted`
```json
{
  "event": "ReviewSubmitted",
  "timestamp": "2025-06-09T10:00:00Z",
  "data": {
    "review_id": 42,
    "course_id": 5,
    "student_id": "uuid-mahasiswa",
    "major": "Informatika",
    "semester": 3,
    "difficulty": 8
  }
}
```

### `DifficultyScoreUpdated`
Dipublish ketika rata-rata difficulty berubah > 0.5 poin setelah review baru masuk.
```json
{
  "event": "DifficultyScoreUpdated",
  "timestamp": "2025-06-09T10:00:01Z",
  "data": {
    "course_id": 5,
    "course_code": "IF301",
    "new_avg_difficulty": 7.8,
    "previous_avg_difficulty": 7.2,
    "total_reviews": 24
  }
}
```

**Consumer:** Recommendation Service

---

## 🔗 Koneksi ke Service Lain

```
Course Review Service (Laravel)
  │
  ├── [RabbitMQ PUBLISH] ──→ Recommendation Service
  │     event: ReviewSubmitted, DifficultyScoreUpdated
  │
  └── [REST Internal] ←── Recommendation Service
        GET /internal/courses/difficulty
```

Autentikasi menggunakan JWT shared-secret yang sama dengan User Service.

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

EXPOSE 3002
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=3002"]
```

### `.env`
```env
APP_PORT=3002
DB_CONNECTION=mysql
DB_HOST=mysql-course
DB_PORT=3306
DB_DATABASE=coursedb
DB_USERNAME=user
DB_PASSWORD=password

RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_EXCHANGE=student-survival-hub

JWT_SECRET=supersecretkey123
```

### Docker Compose Snippet
```yaml
course-review-service:
  build: ./course-review-service
  ports: ["3002:3002"]
  environment:
    DB_HOST: mysql-course
    DB_DATABASE: coursedb
    RABBITMQ_HOST: rabbitmq
    JWT_SECRET: ${JWT_SECRET}
  depends_on:
    mysql-course:
      condition: service_healthy
    rabbitmq:
      condition: service_healthy
  networks: [survival-hub-net]

mysql-course:
  image: mysql:8.0
  environment:
    MYSQL_DATABASE: coursedb
    MYSQL_USER: user
    MYSQL_PASSWORD: password
    MYSQL_ROOT_PASSWORD: rootpassword
  volumes:
    - mysql_course_data:/var/lib/mysql
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
git clone https://github.com/<org>/course-review-service.git && cd course-review-service

# 2. Install dependencies
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Jalankan MySQL + RabbitMQ
docker run -d --name mysql-dev \
  -e MYSQL_DATABASE=coursedb -e MYSQL_USER=user \
  -e MYSQL_PASSWORD=password -e MYSQL_ROOT_PASSWORD=root \
  -p 3306:3306 mysql:8.0

docker run -d --name rabbitmq-dev \
  -p 5672:5672 -p 15672:15672 rabbitmq:3-management

# 5. Migrasi + seed
php artisan migrate
php artisan db:seed

# 6. Jalankan service
php artisan serve --port=3002

# 7. Test submit review (akan trigger publish event)
curl -X POST http://localhost:3002/api/reviews \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <JWT>" \
  -d '{"course_id":1,"lecturer_id":1,"difficulty":8,"teaching_rating":4,"tips":"Banyak latihan soal","semester_taken":3,"academic_year":"2024/2025"}'
```

Cek event di RabbitMQ Management: `http://localhost:15672` (guest/guest)

---

## ✅ Acceptance Criteria

| # | Kriteria | Status |
|---|---|---|
| AC-1 | `GET /api/courses` return daftar MK beserta avg_difficulty | ☐ |
| AC-2 | `GET /api/courses/:id/reviews` return semua review + tips & strategi | ☐ |
| AC-3 | `POST /api/reviews` menyimpan review ke MySQL | ☐ |
| AC-4 | Setelah `POST /api/reviews`, event `ReviewSubmitted` ter-publish ke RabbitMQ | ☐ |
| AC-5 | Jika avg difficulty berubah > 0.5, event `DifficultyScoreUpdated` ter-publish | ☐ |
| AC-6 | Request tanpa JWT ditolak (HTTP 401) | ☐ |
| AC-7 | User hanya bisa edit/hapus review milik sendiri (HTTP 403) | ☐ |
| AC-8 | Service berjalan di Docker container terpisah port 3002 | ☐ |
| AC-9 | MySQL di container terpisah dengan volume persisten | ☐ |
| AC-10 | Endpoint internal `/internal/courses/difficulty` bisa diakses Recommendation Service | ☐ |

---

## 📝 To-Do List

### Setup
- [ ] Init project Laravel 11
- [ ] Struktur folder (routes, controllers, models, migrations)
- [ ] Buat `Dockerfile` dan `.env.example`

### Database
- [ ] Migration tabel `courses`, `lecturers`, `course_reviews`
- [ ] Seeder data dummy MK & dosen
- [ ] Eloquent Model + relasi (Course hasMany Reviews, Lecturer hasMany Reviews)

### REST API
- [ ] CRUD endpoint courses
- [ ] CRUD endpoint reviews (+ auth middleware)
- [ ] Endpoint lecturers
- [ ] Middleware validasi JWT (shared secret)
- [ ] Endpoint internal difficulty

### Message Broker
- [ ] Install `php-amqplib/php-amqplib`
- [ ] Buat class `RabbitMQPublisher`
- [ ] Publish `ReviewSubmitted` setelah review tersimpan
- [ ] Logika cek perubahan avg difficulty
- [ ] Publish `DifficultyScoreUpdated` jika perubahan > 0.5

### Dokumentasi
- [ ] Postman Collection semua endpoint
- [ ] Test publish event + pastikan Recommendation Service menerima
- [ ] Test semua Acceptance Criteria

---

## 📁 Struktur Folder

```
course-review-service/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── CourseController.php
│   │   │   ├── ReviewController.php
│   │   │   └── LecturerController.php
│   │   └── Middleware/
│   │       └── JwtMiddleware.php
│   ├── Models/
│   │   ├── Course.php
│   │   ├── Lecturer.php
│   │   └── CourseReview.php
│   └── Services/
│       └── RabbitMQPublisher.php
├── database/migrations/
├── database/seeders/
├── routes/api.php
├── Dockerfile
├── .env.example
├── composer.json
└── README.md
```

---

## 🔗 Links

- **GitHub Repo:** `https://github.com/<org>/course-review-service`
- **Postman Collection:** `<link>`
- **RabbitMQ Management:** `http://localhost:15672`
