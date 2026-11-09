<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $guru = User::updateOrCreate(
            ['email' => 'guru@example.com'],
            ['name' => 'Bu Sari', 'role' => 'guru', 'password' => 'password'],
        );

        User::updateOrCreate(
            ['email' => 'siswa@example.com'],
            ['name' => 'Andi Pratama', 'role' => 'siswa', 'password' => 'password'],
        );

        if (Task::query()->exists()) {
            return;
        }

        $tasks = [
            ['judul' => 'Latihan Soal Aljabar', 'deskripsi' => 'Kerjakan 20 soal aljabar pada buku paket halaman 45-48.', 'tenggat' => now()->addDays(3)->toDateString(), 'kelas' => 'X IPA 1', 'status' => 'belum'],
            ['judul' => 'Merangkum Bab Sistem Tata Surya', 'deskripsi' => 'Buat rangkuman minimal dua halaman tentang sistem tata surya.', 'tenggat' => now()->addDays(5)->toDateString(), 'kelas' => 'X IPA 1', 'status' => 'belum'],
            ['judul' => 'Presentasi Sejarah Kemerdekaan', 'deskripsi' => 'Siapkan slide presentasi kelompok tentang peristiwa sekitar proklamasi.', 'tenggat' => now()->addDays(7)->toDateString(), 'kelas' => 'XI IPS 2', 'status' => 'selesai'],
            ['judul' => 'Praktikum Uji Asam Basa', 'deskripsi' => 'Lengkapi laporan praktikum dan jawab pertanyaan analisis.', 'tenggat' => now()->subDays(2)->toDateString(), 'kelas' => 'XI IPA 3', 'status' => 'belum'],
            ['judul' => 'Menulis Teks Argumentasi', 'deskripsi' => 'Tulis teks argumentasi bertema lingkungan sepanjang 500 kata.', 'tenggat' => now()->addDays(2)->toDateString(), 'kelas' => 'XII IPA 1', 'status' => 'selesai'],
            ['judul' => 'Kuis Kosakata Bahasa Inggris', 'deskripsi' => 'Hafalkan 50 kosakata unit 4 untuk kuis mendatang.', 'tenggat' => now()->addDays(4)->toDateString(), 'kelas' => 'X IPA 2', 'status' => 'belum'],
            ['judul' => 'Analisis Puisi Chairil Anwar', 'deskripsi' => 'Analisis unsur intrinsik dan ekstrinsik salah satu puisi.', 'tenggat' => now()->addDays(6)->toDateString(), 'kelas' => 'XI IPA 1', 'status' => 'belum'],
            ['judul' => 'Latihan Passing Bola Basket', 'deskripsi' => 'Catat hasil latihan passing selama satu minggu.', 'tenggat' => now()->addDays(9)->toDateString(), 'kelas' => 'XII IPS 1', 'status' => 'selesai'],
            ['judul' => 'Membuat Mind Map Ekosistem', 'deskripsi' => 'Buat mind map komponen ekosistem beserta interaksinya.', 'tenggat' => now()->addDays(1)->toDateString(), 'kelas' => 'X IPA 2', 'status' => 'belum'],
            ['judul' => 'Tugas Program Perulangan', 'deskripsi' => 'Buat program dengan perulangan dan percabangan menggunakan bahasa pilihan.', 'tenggat' => now()->addDays(8)->toDateString(), 'kelas' => 'XI IPA 3', 'status' => 'belum'],
            ['judul' => 'Laporan Studi Lapangan', 'deskripsi' => 'Susun laporan kunjungan industri secara berkelompok.', 'tenggat' => now()->subDays(5)->toDateString(), 'kelas' => 'XII IPS 2', 'status' => 'selesai'],
            ['judul' => 'Ringkasan Berita Ekonomi', 'deskripsi' => 'Pilih satu berita ekonomi terbaru lalu buat ringkasannya.', 'tenggat' => now()->addDays(10)->toDateString(), 'kelas' => 'XII IPS 1', 'status' => 'belum'],
        ];

        foreach ($tasks as $task) {
            $guru->tasks()->create($task);
        }
    }
}
