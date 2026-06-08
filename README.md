<div align="center">

# 🎓 Campus Lens

### *See your campus life clearly — from the eyes of those who've been there.*

> Platform pendamping akademik mahasiswa berbasis microservices yang mengintegrasikan pengalaman senior, jalur karier, dan rekomendasi personal secara real-time.

![Status](https://img.shields.io/badge/Status-Development-success)
![Architecture](https://img.shields.io/badge/Architecture-Microservices-blue)
![GraphQL](https://img.shields.io/badge/API-REST%20%2B%20GraphQL-purple)
![Messaging](https://img.shields.io/badge/Messaging-RabbitMQ-orange)

</div>


## 📑 Table of Contents

* [Tentang Campus Lens](#-tentang-campus-lens)
* [Fitur Utama](#-fitur-utama)
* [Arsitektur Sistem](#-arsitektur-sistem)
* [Detail Microservices](#-detail-microservices)
* [Flow Sistem](#-flow-lengkap)
* [Stack Teknologi](#-stack-teknologi)
* [Database Schema](#-skema-database)
* [Event Driven Architecture](#-event-driven-architecture)
* [Deployment](#-menjalankan-seluruh-sistem)
* [Testing Demo](#-testing-demo-flow)
* [Struktur Repository](#-struktur-repositori)

---

## 🚀 Quick Overview

Campus Lens adalah platform pendamping akademik mahasiswa berbasis microservices yang mengintegrasikan pengalaman senior, roadmap karier, organisasi kampus, dan sistem rekomendasi personal dalam satu ekosistem terintegrasi.

### Tujuan Utama

* Membantu mahasiswa mengambil keputusan akademik berbasis data.
* Mengurangi ketidakpastian dalam memilih mata kuliah.
* Membantu mahasiswa menyusun jalur karier sejak dini.
* Menjadi pusat knowledge-sharing antar angkatan.
* Memberikan rekomendasi personal yang terus diperbarui secara real-time.

### Teknologi Integrasi yang Digunakan

| Komponen          | Teknologi          |
| ----------------- | ------------------ |
| API Communication | REST API           |
| Query Language    | GraphQL            |
| Event Messaging   | RabbitMQ           |
| Containerization  | Docker             |
| API Gateway       | Nginx              |
| Authentication    | JWT                |
| Databases         | PostgreSQL & MySQL |

---

## 🏗️ Karakteristik Enterprise Architecture

Project ini mengimplementasikan konsep Enterprise Application Integration (EAI) melalui:

* Microservices Architecture
* Database per Service Pattern
* API Gateway Pattern
* Event-Driven Architecture
* Publish / Subscribe Messaging
* REST Integration
* GraphQL Integration
* Containerized Deployment

---

## 📡 Integrasi Antar Service

### REST API

Digunakan untuk komunikasi sinkron antar service.

Contoh:

```http
GET /internal/students/:id/profile
GET /internal/roadmap/phases
GET /api/courses/:id/reviews
```

### GraphQL

#### Hasura GraphQL

Digunakan pada User Service untuk:

* Query profil mahasiswa
* Subscription perubahan data mahasiswa

#### Lighthouse GraphQL

Digunakan pada Career Roadmap Service untuk:

* Query roadmap karier
* Mengurangi over-fetching data

#### Apollo GraphQL

Digunakan pada Recommendation Service untuk:

* Query rekomendasi
* Mutation pembaruan rekomendasi

### RabbitMQ

Digunakan sebagai message broker untuk komunikasi asynchronous.

#### Published Events

* ReviewSubmitted
* DifficultyScoreUpdated
* RecommendationUpdated

#### Consumed Events

* ReviewSubmitted
* DifficultyScoreUpdated

---

## 🎯 Business Value

Campus Lens memberikan nilai bisnis melalui:

* Penyediaan informasi akademik yang terstruktur.
* Peningkatan kesiapan karier mahasiswa.
* Sentralisasi pengalaman mahasiswa lintas angkatan.
* Rekomendasi yang selalu relevan berdasarkan aktivitas terbaru pengguna.
* Pengambilan keputusan yang lebih cepat dan akurat.

---

## ⚡ Non Functional Requirements

| Kategori        | Target                    |
| --------------- | ------------------------- |
| Availability    | ≥ 99%                     |
| Response Time   | < 2 detik                 |
| Authentication  | JWT                       |
| Authorization   | Role Based Access Control |
| Scalability     | Horizontal Scaling        |
| Reliability     | RabbitMQ Event Queue      |
| Maintainability | Independent Services      |
| Deployment      | Docker Compose            |

---

## 🔐 Security

* JWT Authentication
* Password Hashing menggunakan bcrypt
* Environment Variable Management
* Internal Service Communication
* Role-Based Authorization
* Isolated Database per Service

---

## 🛡️ Fault Tolerance

Jika Recommendation Service tidak aktif:

1. Event tetap tersimpan di RabbitMQ.
2. Event tidak hilang selama broker aktif.
3. Event akan diproses kembali saat service hidup.
4. Konsistensi data tetap terjaga.

---

## 📦 Service Responsibility Matrix

| Domain                | Service                |
| --------------------- | ---------------------- |
| Authentication        | User Service           |
| Student Profile       | User Service           |
| Course Review         | Course Review Service  |
| Career Roadmap        | Career Roadmap Service |
| Recommendation Engine | Recommendation Service |
| Event Messaging       | RabbitMQ               |
| API Gateway           | Nginx                  |

---

## 📌 Catatan

Project ini dibuat sebagai implementasi konsep Integrasi Aplikasi Enterprise (IAE) dengan menggabungkan beberapa pola integrasi modern:

* REST API Integration
* GraphQL Integration
* Event-Driven Architecture
* Message-Oriented Middleware
* Microservices Architecture
* Containerized Deployment

Seluruh service dapat dijalankan secara independen namun tetap terintegrasi dalam satu ekosistem melalui REST, GraphQL, dan RabbitMQ.
---