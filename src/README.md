# BTS Recruitment API

REST API Laravel untuk autentikasi dan manajemen produk. Aplikasi menggunakan MySQL sebagai database, Redis sebagai cache, Laravel Sanctum untuk token authentication, serta Resource/Service/Repository sebagai pemisah layer aplikasi.

## Teknologi

- PHP 8.3+
- Laravel 13
- MySQL 8.4
- Redis 7
- Laravel Sanctum
- Docker Compose

## Menjalankan Aplikasi

Dari root repository:

```bash
make up
make migrate
```

API tersedia di:

```text
http://localhost:8080/api
```

Perintah lain yang tersedia:

```bash
make down       # menghentikan container
make logs       # melihat log Docker
make shell      # masuk ke container app
make fresh      # migrate:fresh --seed
make test       # menjalankan test Laravel
make frontend   # menjalankan service frontend Vite
```

Konfigurasi Redis pada `.env`:

```env
CACHE_STORE=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

## Struktur Aplikasi

```text
app/
├── Http/
│   ├── Controllers/       # orkestrasi request dan response
│   ├── Requests/           # validasi endpoint
│   └── Resources/          # format response JSON
├── Models/                 # model Eloquent
├── Repositories/          # query dan persistence database
└── Services/               # aturan bisnis, token, audit, dan cache
```

`ProductRepository` menggunakan Redis untuk cache list dan detail produk. Cache menggunakan versioned key sehingga perubahan produk tidak menghapus cache aplikasi lain.

Operasi create, update, delete, dan register berjalan di dalam database transaction. API juga memiliki rate limit:

- Register dan login: maksimal 3 request per menit per IP
- Create, update, dan delete produk: maksimal 1 request per 5 detik per user/IP

Jika batas tercapai, API mengembalikan HTTP `429 Too Many Requests`.

## Authentication API

### Register

`POST /api/auth/register`

Request:

```json
{
  "username": "jhon_doe",
  "password": "supersecret",
  "password_confirmation": "supersecret"
}
```

Kolom `username` API disimpan ke kolom `name` karena schema tabel `users` menggunakan kolom `name`, bukan `username`.

Response berhasil mengandung:

```json
{
  "user": {},
  "authentication_token": "token-access",
  "refresh_token": "token-refresh"
}
```

### Login

`POST /api/auth/login`

Request:

```json
{
  "username": "jhon_doe",
  "password": "supersecret"
}
```

Gunakan token pada endpoint yang memerlukan authorization:

```http
Authorization: Bearer <authentication_token>
Accept: application/json
```

## Products API

### List Produk

`GET /api/products`

Query parameter yang tersedia:

| Parameter | Keterangan |
| --- | --- |
| `search` | mencari berdasarkan `title` |
| `category` | memfilter kategori |
| `limit` | jumlah data per halaman, maksimal 100 |
| `page` | nomor halaman |

Contoh:

```text
GET /api/products?search=shirt&category=Clothes&limit=10&page=1
```

### Detail Produk

`GET /api/products/{id}`

Mengembalikan detail produk. Produk yang tidak ditemukan menghasilkan response `404` JSON.

### Tambah Produk

`POST /api/products`

Memerlukan Bearer token.

```json
{
  "title": "Awesome T-Shirt",
  "price": 99.99,
  "description": "High-quality cotton t-shirt",
  "category": "Clothes",
  "images": [
    "https://placehold.co/640x480"
  ]
}
```

Field wajib: `title`, `price`, `category`, dan `images`. Array `images` harus memiliki minimal satu URL.

### Ubah Produk

`PUT /api/products/{id}`

Memerlukan Bearer token. Semua field produk bersifat opsional sehingga update parsial didukung.

### Hapus Produk

`DELETE /api/products/{id}`

Memerlukan Bearer token dan mengembalikan pesan sukses setelah produk dihapus.

## Schema Produk

Produk memiliki field:

```text
id
title
price
description
category
images
created_at
created_by
created_by_id
updated_at
updated_by
updated_by_id
```

Field `created_by` dan `updated_by` diisi dari user yang sedang terautentikasi.

## Seeder

Seeder membuat user contoh berikut:

```text
Name     : Jhon Doe
Email    : jhon.doe@example.com
Password : supersecret
```

Jalankan melalui:

```bash
make fresh
```

## Testing

```bash
make test
```

Coverage feature API tersedia pada:

```text
tests/Feature/Api/AuthenticationTest.php
tests/Feature/Api/ProductAuthorizationTest.php
```

Pastikan migrasi sudah dijalankan sebelum menggunakan endpoint produk. Tanpa migrasi, tabel `products` dan `personal_access_tokens` belum tersedia.

Atau jalankan test tertentu di dalam container:

```bash
make artisan cmd="test --filter=ExampleTest"
```
