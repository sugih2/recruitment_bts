# Laravel Docker Starter (WSL2) — Struktur Senior

## 1. Prasyarat di WSL2
```bash
# Cek Docker sudah bisa dipakai dari dalam WSL (Docker Desktop WSL integration aktif,
# atau Docker Engine native di WSL)
docker --version
docker compose version
```
Jika belum ada Docker: install Docker Desktop di Windows lalu aktifkan
**Settings > Resources > WSL Integration** untuk distro WSL yang dipakai.
Kalau mau tanpa Docker Desktop, bisa install Docker Engine langsung di dalam WSL
(distro Ubuntu) via `apt`.

## 2. Siapkan project
```bash
cd ~/projects
mkdir myapp && cd myapp
# taruh semua file starter kit ini di root folder ini
cp .env.docker.example .env
mkdir src   # nanti diisi source code Laravel
```

## 3. Build image & install Laravel (API-only, tanpa UI)
```bash
make build          # build image PHP 8.4
docker compose up -d mysql redis   # nyalakan dependency dulu
make install        # laravel new . --no-authentication --no-node -> tanpa frontend scaffold
make up             # nyalakan semua service (app, nginx, mysql, redis)
```
Catatan: flag `--api` sudah dihapus dari `laravel/installer` versi terbaru
(^5.32). Sekarang cukup **tidak memilih** frontend apa pun (`--react`,
`--vue`, `--svelte`, `--livewire`) supaya dapat skeleton polos tanpa
Blade/Inertia. Flag yang dipakai di sini:
- `--no-authentication` → skip scaffolding login/register/Breeze bawaan,
  karena auth API akan kita tulis sendiri (lihat modul Auth di implementasi
  coding test)
- `--no-node` → skip install dependency npm (tidak relevan untuk backend saja)

Skeleton hasil install tetap punya `routes/web.php` kosong dan
`resources/views/welcome.blade.php` default — keduanya aman dihapus manual
kalau mau benar-benar bersih.

`make install` juga otomatis menjalankan `php artisan install:api`, yang:
- membuat `routes/api.php` dan mendaftarkannya di `bootstrap/app.php`
  (`->withRouting(..., api: __DIR__.'/../routes/api.php', ...)`)
- memasang package `laravel/sanctum` untuk auth berbasis token

Karena tidak ada UI, container `node` (profile `frontend`) tidak perlu dijalankan sama sekali —
abaikan `make frontend` kecuali suatu saat butuh admin panel terpisah.

Akses di `http://localhost:8080/api/...` (atau port sesuai `APP_PORT` di `.env`).

## 4. Setelah Laravel ter-install
```bash
cp src/.env.example src/.env  # lalu selaraskan DB_HOST=mysql, DB_PORT=3306, dst.
make artisan cmd="key:generate"
make migrate
```

## 5. Struktur folder "senior" di dalam `src/app`
Alih-alih menumpuk semua Controller/Model dalam satu folder generik ala default
Laravel, pisahkan per **modul/domain** supaya scalable dan gampang di-maintain
oleh tim:

```
src/
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Middleware/
│   │   └── Kernel.php
│   ├── Providers/
│   └── Modules/                 <-- inti struktur senior
│       ├── User/
│       │   ├── Http/
│       │   │   ├── Controllers/
│       │   │   ├── Requests/
│       │   │   └── Resources/
│       │   ├── Models/
│       │   ├── Services/
│       │   ├── Repositories/
│       │   ├── Policies/
│       │   ├── Events/
│       │   ├── Listeners/
│       │   ├── Routes/
│       │   │   ├── api.php
│       │   │   └── web.php
│       │   └── Database/
│       │       ├── Migrations/
│       │       ├── Factories/
│       │       └── Seeders/
│       └── (Modul lain: Payroll/, Procurement/, dst — kosongkan dulu)
├── bootstrap/
├── config/
├── database/                    <-- tetap ada untuk migration global/shared
├── routes/
│   └── web.php                  <-- hanya bootstrap load Modules/*/Routes
├── tests/
│   ├── Unit/
│   └── Feature/
└── ...
```

**Prinsip yang ditegakkan sejak awal:**
- Setiap modul **self-contained**: Controller, Model, Service, Repository,
  Route, Migration miliknya sendiri hidup di dalam foldernya.
- Business logic **tidak boleh** ditulis langsung di Controller — selalu lewat
  `Service` (dan `Repository` untuk akses data jika query kompleks).
- Route per modul di-`require` dari `routes/web.php` atau lewat
  `RouteServiceProvider`, bukan ditulis manual satu file panjang.
- Setiap modul boleh punya `Providers/{Module}ServiceProvider.php` sendiri
  yang di-register di `bootstrap/providers.php`, supaya modul bisa "dicabut"
  tanpa merusak modul lain.
- Isi file (`Controller`, `Model`, dll) sengaja dikosongkan/skeleton dulu di
  starter ini — konten disesuaikan begitu konteks aplikasi ditentukan.

Kalau nanti mau strukturnya di-generate otomatis pakai package
(`nwidart/laravel-modules`) daripada bikin manual, tinggal bilang — bisa
disesuaikan.

## 6. Command harian
| Perintah          | Fungsi                                   |
|--------------------|-------------------------------------------|
| `make up`          | start semua container                     |
| `make down`        | stop semua container                      |
| `make shell`       | masuk shell container `app`               |
| `make artisan cmd="migrate"` | jalankan artisan command apa saja |
| `make fresh`       | migrate:fresh --seed                      |
| `make frontend`    | start container node (vite dev server)    |
| `make logs`        | tail logs semua service                   |
