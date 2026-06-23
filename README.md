# REVORA - Revenue Estimation, Visualization, Optimization, Reporting, and Analytics (Laravel Frontend)

Aplikasi Web Utama untuk sistem **REVORA** (sistem prediksi & optimasi pendapatan retribusi parkir Dinas Perhubungan Kota Cirebon). Aplikasi ini bertindak sebagai antarmuka pengguna (frontend), pengelola database MySQL, dan penyedia otentikasi multi-role.

Sistem ini berkomunikasi secara asynchronous/RESTful dengan mesin kecerdasan buatan (FastAPI Python) menggunakan tanda tangan **JWT (JSON Web Token)** dinamis dengan masa aktif token yang disinkronkan selama 6 jam.

---

## 🏗️ Fitur & Role-Based Access Control (RBAC)

Aplikasi ini menggunakan `spatie/laravel-permission` untuk membagi hak akses ke dalam 3 peran utama:

1.  **Operator UPT Parkir**:
    -   CRUD Master Data (Rayon, Juru Parkir, Hari Libur).
    -   Pencatatan pendapatan transaksi parkir harian (Input Manual & Import Excel).
    -   Memicu pelatihan ulang model **SVR Standar** pada backend.
    -   Menjalankan tuning hyperparameter menggunakan **Grid Search** dan **Grey Wolf Optimizer (GWO)** secara riil.
2.  **Kepala UPT Parkir**:
    -   Pemantauan performa model (Akurasi MAPE, RMSE, MAE, R²).
    -   Dashboard visualisasi tren prediksi vs realisasi parkir.
    -   Cetak Laporan bulanan/tahunan (Ekspor PDF).
3.  **Kepala Dinas Perhubungan (Dishub)**:
    -   Dashboard eksekutif tahunan (Realisasi vs Target Anggaran Pendapatan).
    -   Pemantauan tren optimasi parameter model SVR.
    -   Cetak Laporan (Ekspor PDF).

---

## 📁 Struktur Utama Aplikasi
```text
web-app/
├── app/
│   ├── Http/Controllers/    # Controller untuk masing-masing Role (Operator, UPT, Dishub)
│   ├── Models/              # Model data Eloquent (Rayon, Pendapatan, ModelRun, dll)
│   └── Services/            # FastApiService (API Client penghubung ke FastAPI)
├── database/
│   ├── migrations/          # Schema tabel database MySQL
│   └── seeders/             # Data bawaan untuk wilayah Rayon dan Akun Default
├── resources/
│   ├── views/               # Halaman antarmuka Blade per-role
│   └── css/js/              # Aset frontend (di-bundle menggunakan Vite)
└── tests/                   # Fitur & Unit testing PHPUnit
```

---

## 🚀 Cara Menjalankan Secara Mandiri (Tanpa Docker Root)

Jika Anda ingin menjalankan repositori Laravel ini secara mandiri pada OS lokal Anda (pastikan backend FastAPI sudah menyala di port `8000` dan MySQL di port `3306`):

1. **Install Dependensi PHP**:
   ```bash
   composer install
   ```
2. **Install Dependensi Assets**:
   ```bash
   npm install
   ```
3. **Konfigurasi Environment**:
   Salin berkas `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
   *Sesuaikan koneksi database MySQL (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) dan arahkan API URL ke server FastAPI Anda:*
   ```env
   FASTAPI_URL=http://127.0.0.1:8000
   PYTHON_ML_API_URL=http://127.0.0.1:8000
   ```
4. **Generate Key & Migrasi Database**:
   ```bash
   php artisan key:generate
   php artisan migrate:fresh --seed
   ```
5. **Compile Assets & Jalankan Web Server**:
   ```bash
   npm run build
   php artisan serve --port=8001
   ```
6. Akses antarmuka di [http://127.0.0.1:8001](http://127.0.0.1:8001).

---

## 🔐 Akun Uji Coba Default

| Username | Password | Role / Jabatan |
| :--- | :--- | :--- |
| **`operator`** | `password` | Operator UPT Parkir |
| **`kepala_upt`** | `password` | Kepala UPT Parkir |
| **`kepala_dishub`** | `password` | Kepala Dinas Perhubungan |

---

## 🧪 Menjalankan Pengujian (Testing)

Aplikasi dilengkapi dengan Feature & Unit tests lengkap untuk memvalidasi alur bisnis, otentikasi role, serta integrasi API:
```bash
php artisan test
```
*(Menjalankan 19 skenario pengujian dengan PHPUnit).*
