# 🎓 Campus Lens

### *See your campus life clearly — from the eyes of those who've been there.*

> Platform pendamping akademik mahasiswa berbasis microservices yang mengintegrasikan pengalaman senior, jalur karier, dan rekomendasi personal secara real-time.

![Status](https://img.shields.io/badge/Status-Development-success)
![Architecture](https://img.shields.io/badge/Architecture-Microservices-blue)
![GraphQL](https://img.shields.io/badge/API-REST%20%2B%20GraphQL-purple)
![Messaging](https://img.shields.io/badge/Messaging-RabbitMQ-orange)

---

# 🚀 Quick Overview

Campus Lens merupakan platform berbasis microservices yang membantu mahasiswa mengambil keputusan akademik dan karier melalui pengalaman nyata dari senior.

Platform menyediakan:

* Review mata kuliah dan dosen
* Informasi organisasi kampus
* Jalur karier yang terstruktur
* Sistem rekomendasi personal
* Integrasi REST API
* Integrasi GraphQL
* Event-driven architecture menggunakan RabbitMQ

Sistem dibangun menggunakan empat microservices utama:

| Service                | Teknologi                             |
| ---------------------- | ------------------------------------- |
| User Service           | Node.js + PostgreSQL + Hasura         |
| Course Review Service  | Laravel + MySQL                       |
| Career Roadmap Service | Laravel + MySQL + Lighthouse          |
| Recommendation Service | Node.js + Apollo GraphQL + PostgreSQL |

---

# 📖 Tentang Campus Lens

Mahasiswa baru sering menghadapi berbagai tantangan dalam menentukan jalur akademik maupun karier. Informasi yang dibutuhkan sebenarnya tersedia, namun tersebar di berbagai media seperti grup WhatsApp, media sosial, hingga pengalaman pribadi senior yang tidak terdokumentasi dengan baik.

Campus Lens hadir sebagai solusi untuk mengintegrasikan seluruh informasi tersebut ke dalam satu platform yang terpusat.

Melalui Campus Lens, mahasiswa dapat:

* Memahami tingkat kesulitan mata kuliah
* Mengetahui pengalaman mahasiswa terdahulu
* Menyusun roadmap pengembangan diri
* Menentukan jalur karier yang sesuai
* Mendapatkan rekomendasi personal berbasis data

Dengan pendekatan microservices dan event-driven architecture, rekomendasi yang diberikan dapat diperbarui secara otomatis setiap kali terdapat data baru yang masuk ke dalam sistem.

---

# 🎯 Tujuan Sistem

1. Membantu mahasiswa mengambil keputusan akademik berbasis data.
2. Mengurangi ketidakpastian dalam pemilihan mata kuliah.
3. Membantu mahasiswa mempersiapkan karier sejak dini.
4. Menjadi pusat knowledge sharing antar angkatan.
5. Menyediakan rekomendasi personal secara real-time.

---

# 🏗️ Karakteristik Arsitektur

Sistem mengimplementasikan konsep Enterprise Application Integration (EAI) melalui:

* Microservices Architecture
* API Gateway Pattern
* Database per Service
* Event Driven Architecture
* REST Integration
* GraphQL Integration
* Publish/Subscribe Messaging
* Containerization menggunakan Docker

---

# 📡 Integrasi yang Diimplementasikan

## REST API

Digunakan untuk:

* Komunikasi Client ke Service
* Komunikasi Internal antar Service

Contoh:

GET /api/students/:id

GET /internal/students/:id/profile

---

## GraphQL

### Hasura

Digunakan pada User Service untuk:

* Query Data Mahasiswa
* Real-time Subscription

### Lighthouse

Digunakan pada Career Roadmap Service untuk:

* Query Roadmap Karier
* Partial Data Fetching

### Apollo Server

Digunakan pada Recommendation Service untuk:

* Query Rekomendasi
* Mutation Pembaruan Rekomendasi

---

## RabbitMQ

Digunakan sebagai message broker untuk komunikasi asynchronous.

### Published Events

* ReviewSubmitted
* DifficultyScoreUpdated
* RecommendationUpdated

### Consumed Events

* ReviewSubmitted
* DifficultyScoreUpdated

---

# 📨 Event Driven Architecture

Exchange:

student-survival-hub (topic)

Routing Key:

review.submitted

course.difficulty.updated

recommendation.updated

Flow utama:

Course Review Service
↓ Publish

RabbitMQ Exchange
↓ Consume

Recommendation Service
↓ Publish

RecommendationUpdated

---

# 📦 Container Summary

Total Container: 10

| Container              | Fungsi                  |
| ---------------------- | ----------------------- |
| nginx                  | API Gateway             |
| user-service           | Authentication          |
| postgres-user          | User Database           |
| hasura                 | User GraphQL            |
| course-review-service  | Review Management       |
| mysql-course           | Review Database         |
| career-roadmap-service | Career Management       |
| mysql-career           | Career Database         |
| recommendation-service | Recommendation Engine   |
| postgres-rec           | Recommendation Database |
| rabbitmq               | Message Broker          |

---

# 🎓 Nilai Enterprise yang Diimplementasikan

✅ Service Isolation

✅ Independent Database

✅ Event Driven Communication

✅ REST Integration

✅ GraphQL Integration

✅ API Gateway

✅ Containerization

✅ Real-Time Recommendation Update

✅ Asynchronous Messaging

✅ Scalability Ready

---

# 📌 Catatan

Project ini dibuat sebagai Tugas Besar Integrasi Aplikasi Enterprise (IAE) dengan tujuan menunjukkan implementasi integrasi sistem enterprise modern menggunakan kombinasi:

* REST API
* GraphQL
* RabbitMQ
* Docker
* Microservices Architecture

dalam satu ekosistem aplikasi yang saling terhubung.
