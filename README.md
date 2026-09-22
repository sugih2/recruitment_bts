# BTS Recruitment API

REST API untuk sistem recruitment, dibangun dengan Laravel 12 (PHP 8.4), MySQL 8.4, dan Redis — dijalankan sepenuhnya lewat Docker.

## Struktur Proyek

```
bts_recruitment/
├── Makefile              # Shortcut perintah docker
├── docker-compose.yml    # Definisi service (app, nginx, mysql, redis, node)
├── .env                  # Konfigurasi docker-compose (gitignored)
├── .env.example           # Template konfigurasi
├── docker/
│   ├── php/Dockerfile     # Image PHP-FPM custom
│   ├── nginx/default.conf
│   └── mysql/my.cnf
└── src/                   # Source code Laravel (root aplikasi Laravel)
    ├── app/
    ├── routes/
    ├── database/
    └── ...
```

## Prasyarat

- Docker & Docker Compose
- Git

## Instalasi Docker

### Windows dengan WSL2 (Ubuntu) — disarankan

**Opsi A — Docker Desktop (paling mudah)**
1. Download & install [Docker Desktop for Windows](https://www.docker.com/products/docker-desktop/)
2. Saat instalasi, pastikan opsi **"Use WSL 2 based engine"** aktif
3. Buka Docker Desktop → **Settings → Resources → WSL Integration** → aktifkan toggle untuk distro Ubuntu kamu
4. Restart WSL: buka PowerShell, jalankan `wsl --shutdown`, lalu buka ulang terminal Ubuntu

**Opsi B — Docker Engine langsung di WSL2 (tanpa Docker Desktop, lebih ringan)**
```bash
sudo apt-get update
sudo apt-get install ca-certificates curl gnupg -y

sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update

sudo apt-get install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin -y
```

Tambahkan user ke grup `docker` supaya tidak perlu `sudo` tiap perintah:
```bash
sudo usermod -aG docker $USER
newgrp docker
```

Jalankan Docker daemon (kalau pakai Opsi B tanpa systemd aktif):
```bash
sudo service docker start
```

### Verifikasi instalasi

```bash
docker --version
docker compose version
```

## Instalasi Proyek

```bash
git clone https://github.com/sugih2/recruitment_bts.git
cd recruitment_bts
make up
```

Perintah `make up` otomatis:
1. Membuat `.env` dari `.env.example` (kalau belum ada)
2. Mengisi `UID`/`GID` sesuai user host (supaya file yang dibuat container tidak owned `root`)
3. Build & jalankan semua container (app, nginx, mysql, redis)

Setelah container jalan, siapkan Laravel:
```bash
make artisan cmd="key:generate"
make migrate
```

Akses aplikasi di: **http://localhost:8080**

## Perintah Makefile

| Perintah | Fungsi |
|---|---|
| `make up` | Setup env + build & jalankan semua container |
| `make down` | Hentikan semua container |
| `make build` | Build ulang image tanpa cache |
| `make shell` | Masuk ke shell container app |
| `make artisan cmd="..."` | Jalankan artisan, contoh: `make artisan cmd="make:model Post -m"` |
| `make migrate` | Jalankan migration |
| `make fresh` | Migrate fresh + seed |
| `make test` | Jalankan test suite |
| `make logs` | Lihat log semua container |
| `make frontend` | Jalankan container node (Vite dev server) |

## Environment Variables (`.env` di root)

| Variable | Deskripsi | Default |
|---|---|---|
| `APP_PORT` | Port akses aplikasi (nginx) | 8080 |
| `VITE_PORT` | Port Vite dev server | 5173 |
| `DB_DATABASE` | Nama database | laravel |
| `DB_USERNAME` | User MySQL | laravel |
| `DB_PASSWORD` | Password MySQL | secret |
| `DB_PORT` | Port MySQL (host) | 3306 |

> Catatan: `src/.env` (config Laravel) terpisah dari `.env` root, dan harus punya `DB_HOST=mysql` (nama service, bukan `127.0.0.1`).

## API Endpoints

### Auth
| Method | Endpoint | Deskripsi |
|---|---|---|
| POST | `/api/auth/register` | Registrasi user baru |
| POST | `/api/auth/login` | Login, dapat token Sanctum |

### Products
| Method | Endpoint | Auth | Deskripsi |
|---|---|---|---|
| GET | `/api/products` | - | List produk (`search`, `category`, `limit`) |
| GET | `/api/products/{id}` | - | Detail produk |
| POST | `/api/products` | Bearer Token | Buat produk baru |
| PUT | `/api/products/{id}` | Bearer Token | Update produk |
| DELETE | `/api/products/{id}` | Bearer Token | Hapus produk |

Postman collection: `bts_recruitment.postman_collection.json`.

## Troubleshooting

**File yang dibuat container jadi milik `root`**
Pastikan `.env` root punya `UID`/`GID` sesuai `id -u` / `id -g` host, lalu `make build && make up`.

**Error `Connection refused` ke database**
Pastikan `src/.env` punya `DB_HOST=mysql`, bukan `127.0.0.1` — container saling terhubung lewat nama service Docker internal.

## Lisensi

Proyek internal.