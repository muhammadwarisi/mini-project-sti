# TugasKita — Mini Project STI

Aplikasi manajemen tugas kelas dengan dua peran: **guru** dan **siswa**.

- Guru dapat membuat, mengubah, dan menghapus tugas (judul, deskripsi, tenggat, kelas).
- Siswa dapat melihat daftar tugas dan menandai tugas sebagai selesai/belum.
- Daftar tugas mendukung pencarian judul, filter status, dan paginasi.
- Backend berupa REST API Laravel + Sanctum, frontend berupa SPA Vue 3.

## Teknologi

| Bagian    | Teknologi                                      |
| --------- | ---------------------------------------------- |
| Backend   | PHP 8.3+, Laravel 13, Laravel Sanctum, MySQL   |
| Frontend  | Vue 3, Vue Router, Vite 7                      |
| Autentikasi | Bearer token (Laravel Sanctum)                |

## Catatan Penggunaan AI

Bagian **frontend** pada proyek ini dikembangkan dengan bantuan AI. Seluruh hasilnya tetap ditinjau, dijalankan, dan diverifikasi secara manual, termasuk pengujian alur autentikasi, CRUD tugas, pencarian, filter status, dan paginasi.

## Struktur Proyek

```
.
├── backend/    # REST API Laravel
│   ├── app/
│   ├── database/       # migrasi & seeder
│   ├── routes/api.php  # endpoint API
│   └── ...
└── frontend/   # Aplikasi web Vue 3 + Vite
    ├── src/
    │   ├── views/      # halaman login, register, tugas
    │   ├── components/
    │   ├── stores/     # state autentikasi & toast
    │   └── lib/api.js  # client API
    └── ...
```

## Prasyarat

Pastikan perangkat sudah terpasang:

- PHP **8.3+** beserta ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `xml`, `ctype`, `json`
- Composer 2
- MySQL 8+ / MariaDB 10.4+ (atau PostgreSQL / SQLite)
- Node.js **20.19+** atau **22.12+** dan npm

Cek versi:

```bash
php -v
composer -V
node -v
npm -v
```

---

## 1. Menjalankan Backend

```bash
cd backend

# Install dependency PHP
composer install

# Salin konfigurasi environment
cp .env.example .env

# Generate application key
php artisan key:generate
```

Buka `backend/.env`, lalu sesuaikan koneksi database. Contoh untuk MySQL:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini_proyek_sti
DB_USERNAME=root
DB_PASSWORD=
```

Buat database (sesuaikan user/password MySQL):

```bash
mysql -u root -p -e "CREATE DATABASE mini_proyek_sti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Jalankan migrasi sekaligus seeder data contoh:

```bash
php artisan migrate --seed
```

Jalankan server API:

```bash
php artisan serve
```

Backend tersedia di **http://localhost:8000** (endpoint API berawalan `/api`).

> Ingin mengulang dari awal? Gunakan `php artisan migrate:fresh --seed`.

### Opsi cepat memakai SQLite

Jika tidak ingin menyiapkan MySQL, ubah `.env`:

```dotenv
DB_CONNECTION=sqlite
```

Lalu buat file database dan jalankan migrasi:

```bash
touch database/database.sqlite
php artisan migrate --seed
```

---

## 2. Menjalankan Frontend

Buka terminal baru (biarkan server backend tetap berjalan):

```bash
cd frontend

# Install dependency JavaScript
npm install

# Jalankan development server
npm run dev
```

Akses aplikasi di **http://localhost:5173**.

Saat development, Vite otomatis mem-proxy request `/api` ke `http://127.0.0.1:8000`, jadi frontend dan backend bisa berjalan di port berbeda tanpa masalah CORS.

Jika backend berjalan di host/port lain, buat file `frontend/.env`:

```dotenv
# Proxy Vite saat development
VITE_API_PROXY=http://127.0.0.1:8001

# Atau panggil API langsung (tanpa proxy)
# VITE_API_URL=http://localhost:8000/api
```

### Build produksi

```bash
cd frontend
VITE_API_URL=http://localhost:8000/api npm run build
```

Hasil build ada di `frontend/dist/`. Karena build statis tidak memakai proxy Vite, isi `VITE_API_URL` dengan URL backend yang dapat diakses browser.

---

## Akun Demo

Seeder membuat dua akun berikut:

| Peran | Email               | Password   |
| ----- | ------------------- | ---------- |
| Guru  | `guru@example.com`  | `password` |
| Siswa | `siswa@example.com` | `password` |

Anda juga dapat mendaftar akun baru melalui halaman **Daftar** dan memilih peran guru atau siswa.

---

## Alur Penggunaan

1. Buka `http://localhost:5173`, lalu masuk dengan akun demo atau daftar akun baru.
2. **Guru** dapat membuat tugas baru, mengubah, dan menghapus tugas melalui tombol pada kartu tugas.
3. **Siswa** hanya dapat melihat tugas dan menekan tombol **Tandai selesai** / **Tandai belum**.
4. Gunakan kolom pencarian, filter status (Semua / Belum selesai / Selesai), dan navigasi halaman untuk menemukan tugas.

## Hak Akses

| Aksi                          | Guru | Siswa |
| ----------------------------- | :--: | :---: |
| Melihat daftar tugas          |  Ya  |  Ya   |
| Membuat tugas                 |  Ya  | Tidak |
| Mengubah tugas                |  Ya  | Tidak |
| Menghapus tugas               |  Ya  | Tidak |
| Menandai status selesai/belum | Tidak |  Ya   |

Pembatasan juga diterapkan di backend melalui `TaskPolicy`, sehingga siswa tetap mendapat `403 Forbidden` jika mencoba mengubah atau menghapus tugas lewat API.

---

## Ringkasan Endpoint API

Semua respons menggunakan format JSON. Endpoint bertanda auth memerlukan header:

```
Authorization: Bearer <token>
Accept: application/json
```

| Method | Endpoint                     | Akses | Keterangan                          |
| ------ | ---------------------------- | ----- | ----------------------------------- |
| POST   | `/api/register`              | Publik | Registrasi, langsung mendapat token |
| POST   | `/api/login`                 | Publik | Login dan mendapat token            |
| GET    | `/api/user`                  | Auth  | Data pengguna yang sedang login     |
| GET    | `/api/tasks`                 | Auth  | Daftar tugas                        |
| POST   | `/api/tasks`                 | Guru  | Membuat tugas                       |
| PUT    | `/api/tasks/{id}`            | Guru  | Mengubah tugas                      |
| DELETE | `/api/tasks/{id}`            | Guru  | Menghapus tugas                     |
| PATCH  | `/api/tasks/{id}/complete`   | Siswa | Toggle status `belum` / `selesai`   |

Query parameter untuk `GET /api/tasks`:

- `search` — cari berdasarkan judul
- `status` — `belum` atau `selesai`
- `page` — nomor halaman
- `per_page` — jumlah data per halaman (default 10, maksimal 100)

Contoh login dan mengambil daftar tugas:

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"guru@example.com","password":"password"}'
```

```bash
curl "http://localhost:8000/api/tasks?search=aljabar&status=belum&page=1" \
  -H "Authorization: Bearer <token>" \
  -H "Accept: application/json"
```

Validasi input yang gagal mengembalikan status `422` beserta objek `errors`, sedangkan akses tanpa token mengembalikan `401` dan akses tanpa izin mengembalikan `403`.

---

## Perintah Berguna

Backend:

```bash
cd backend
php artisan route:list --path=api   # daftar endpoint API
php artisan migrate:fresh --seed    # reset database + data contoh
php artisan test                    # menjalankan test
php artisan optimize:clear          # bersihkan cache konfigurasi/route
```

Frontend:

```bash
cd frontend
npm run dev       # development server (http://localhost:5173)
npm run build     # build produksi ke folder dist/
npm run preview   # pratinjau hasil build
```

---

## Troubleshooting

- **Frontend menampilkan "Tidak dapat terhubung ke server"** — pastikan backend berjalan di port yang benar dan `VITE_API_PROXY` / `VITE_API_URL` sesuai.
- **Respons `401 Unauthorized`** — token tidak ada atau kedaluwarsa. Logout lalu login kembali.
- **Respons `403 Forbidden`** — akun tidak memiliki peran yang sesuai untuk aksi tersebut.
- **Error koneksi MySQL** — periksa `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` pada `backend/.env`, serta pastikan database sudah dibuat.
- **Perubahan `.env` tidak terbaca** — jalankan `php artisan optimize:clear` lalu restart `php artisan serve`.
- **Port sudah dipakai** — jalankan backend di port lain, misalnya `php artisan serve --port=8001`, lalu sesuaikan `VITE_API_PROXY` di `frontend/.env`.
