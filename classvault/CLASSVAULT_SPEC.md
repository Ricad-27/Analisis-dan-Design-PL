# PROYEK: CLASSVAULT
## Personal Academic File Storage and Sharing Website

Bertindaklah sebagai senior full-stack web developer, software architect, UI/UX designer, dan application security engineer.

Saya ingin membangun website bernama ClassVault, yaitu aplikasi penyimpanan dokumen akademik pribadi yang memungkinkan mahasiswa menyimpan, mengatur, dan membagikan dokumen kepada mahasiswa lain.

Aplikasi ini bukan LMS, bukan sistem penilaian, dan bukan sistem pengumpulan tugas yang kompleks. Fokusnya adalah penyimpanan dokumen pribadi dan berbagi file secara opsional.

Bangun aplikasi yang sederhana, rapi, aman, mudah dipelajari, dan dapat dikembangkan lebih lanjut.

Jangan langsung membuat seluruh aplikasi dalam satu file besar. Kerjakan secara bertahap, gunakan struktur folder yang jelas, dan uji setiap tahap sebelum melanjutkan.

---

# 1. TUJUAN APLIKASI

ClassVault memiliki tujuan:

1. Menyediakan ruang penyimpanan dokumen akademik pribadi.
2. Memungkinkan mahasiswa mengunggah dan mengelola dokumen tugas kuliah.
3. Memungkinkan pemilik membagikan dokumen kepada pengguna lain.
4. Memungkinkan pengguna mencari dan mengunduh dokumen sesuai hak aksesnya.
5. Menyediakan antarmuka yang sederhana dan responsif.
6. Memungkinkan aplikasi dijalankan secara lokal melalui XAMPP.
7. Menyiapkan aplikasi agar dapat di-deploy ke hosting yang mendukung PHP, MySQL, dan penyimpanan file yang sesuai dengan kebutuhan aplikasi.

Gunakan Bahasa Indonesia untuk seluruh antarmuka pengguna.

---

# 2. TEKNOLOGI

Gunakan:

- PHP 8.x yang kompatibel dengan versi PHP pada lingkungan target.
- MySQL atau MariaDB.
- HTML5.
- CSS3.
- JavaScript.
- Bootstrap 5.
- PDO untuk koneksi dan query database.
- Session PHP untuk autentikasi.
- XAMPP untuk pengembangan lokal.

Gunakan JavaScript seperlunya.

Jangan menggunakan React, Node.js, Docker, atau framework backend yang kompleks untuk versi pertama.

Jangan membuat sistem SPA yang rumit.

Pastikan kode dapat dijalankan melalui XAMPP tanpa memerlukan proses build frontend yang kompleks.

Gunakan dependency sesedikit mungkin.

---

# 3. PERAN PENGGUNA

Hanya ada dua peran utama:

## A. Mahasiswa

Mahasiswa dapat:

- Mendaftar.
- Login dan logout.
- Melihat dashboard pribadi.
- Mengunggah dokumen.
- Melihat daftar dokumen miliknya.
- Mencari dokumen miliknya.
- Memfilter dokumen berdasarkan kategori dan privasi.
- Mengubah nama dan deskripsi dokumen sendiri.
- Mengubah kategori dokumen sendiri.
- Mengubah pengaturan privasi dokumen sendiri.
- Mengunduh dokumen sendiri.
- Menghapus dokumen sendiri.
- Melihat dokumen yang dibagikan pengguna lain.
- Mengunduh dokumen yang diizinkan untuk diaksesnya.
- Mengubah informasi profilnya.
- Mengubah password.

Mahasiswa tidak boleh mengubah atau menghapus dokumen milik pengguna lain.

## B. Admin

Admin dapat:

- Login melalui akun yang dibuat secara khusus.
- Melihat dashboard administrasi.
- Melihat daftar akun pengguna.
- Mencari pengguna.
- Melihat informasi dasar akun.
- Menonaktifkan akun yang melanggar ketentuan penggunaan.
- Mengaktifkan kembali akun jika diperlukan.

Admin tidak boleh otomatis memperoleh akses untuk membaca isi seluruh dokumen pribadi pengguna.

Jangan sediakan pendaftaran publik dengan peran admin.

Sediakan cara aman untuk membuat akun admin pertama, misalnya melalui perintah setup CLI atau proses instalasi lokal yang dikunci setelah selesai.

Jangan menanamkan username dan password admin permanen ke dalam source code.

---

# 4. REGISTRASI DAN LOGIN

Formulir pendaftaran mahasiswa berisi:

- Nama tampilan: wajib.
- Institusi atau universitas: wajib.
- NIM: wajib.
- Program studi: wajib.
- Password: wajib.
- Konfirmasi password: wajib.
- Email: opsional.

Kombinasi institusi dan NIM harus unik.

Normalisasi institusi dan NIM sebelum melakukan pemeriksaan duplikasi agar variasi huruf besar/kecil atau spasi tidak menghasilkan akun ganda.

Email harus divalidasi jika diisi.

Jika email diisi, pertimbangkan agar email tidak dapat digunakan oleh beberapa akun jika nantinya diperlukan untuk pemulihan password.

Password harus memiliki persyaratan minimum yang masuk akal dan tidak boleh disimpan sebagai teks biasa.

Gunakan:

- password_hash()
- password_verify()
- prepared statements PDO
- session_regenerate_id() setelah login berhasil
- perlindungan terhadap brute-force
- validasi sisi server
- pesan error yang tidak membocorkan informasi sensitif

Jangan mengandalkan validasi JavaScript saja.

Jangan membuat fitur pemulihan password melalui email pada versi pertama karena email bersifat opsional. Sediakan mekanisme reset password oleh admin yang aman tanpa pernah menampilkan password asli pengguna.

---

# 5. PRIVASI DOKUMEN

Setiap dokumen memiliki tiga pilihan privasi.

## A. Private

Hanya pemilik dokumen yang dapat melihat dan mengunduhnya.

## B. Pengguna login

Semua pengguna yang sudah login dapat melihat dan mengunduh dokumen tersebut.

## C. Publik

Pengunjung tanpa login dan pengguna yang sudah login dapat melihat serta mengunduh dokumen tersebut.

Pengaturan privasi default WAJIB Private.

Gunakan nilai database yang konsisten, misalnya:

- private
- authenticated
- public

Aturan akses harus diperiksa di backend setiap kali dokumen dilihat atau diunduh.

Jangan hanya menyembunyikan tombol atau menu menggunakan CSS atau JavaScript.

Jangan membuat sistem berbagi berdasarkan kelas atau grup pada versi pertama.

Jangan membuat fitur berbagi kepada pengguna tertentu pada versi pertama.

Jangan mengizinkan pengguna mengakses file pribadi hanya dengan mengetahui ID atau URL file.

---

# 6. FITUR DOKUMEN

## A. Upload

Mahasiswa dapat mengunggah file dengan informasi:

- File.
- Nama dokumen.
- Deskripsi opsional.
- Kategori.
- Privasi.

Kategori awal:

1. Tugas Kuliah.
2. Laporan Praktikum.
3. Proposal.
4. Catatan.
5. Materi Belajar.
6. Lainnya.

Batas ukuran awal: 10 MB per file.

Format file yang diizinkan:

- PDF.
- DOCX.
- XLSX.
- PPTX.
- TXT.
- JPG.
- PNG.

Lakukan validasi pada server terhadap:

- Ekstensi file.
- MIME type yang dideteksi server.
- Ukuran file.
- Nama file.
- Hasil proses upload.

Jangan menerima file PHP, executable, atau format yang tidak diizinkan.

Jangan mempercayai nama file, ekstensi, maupun MIME type yang dikirim browser tanpa validasi.

Gunakan nama file penyimpanan internal yang acak dan aman.

Simpan nama asli file secara terpisah untuk ditampilkan kepada pengguna.

Jika upload gagal, bersihkan file sementara agar tidak meninggalkan data yang tidak terpakai.

## B. Daftar dokumen

Tampilkan dokumen milik pengguna dalam bentuk tabel pada desktop dan kartu yang nyaman dibaca pada layar kecil.

Informasi yang ditampilkan:

- Nama dokumen.
- Kategori.
- Ukuran.
- Privasi.
- Tanggal unggah.
- Tanggal pembaruan.
- Menu aksi.

Sediakan:

- Pencarian berdasarkan nama dan deskripsi.
- Filter kategori.
- Filter privasi.
- Pengurutan berdasarkan nama atau tanggal.
- Pagination server-side.

Pastikan pencarian hanya menampilkan dokumen yang memang boleh dilihat oleh pengguna.

## C. Edit dokumen

Pemilik dapat mengubah:

- Nama dokumen.
- Deskripsi.
- Kategori.
- Privasi.

Tidak perlu membuat editor isi PDF, Word, atau Excel.

Untuk memperbarui isi dokumen, pemilik dapat mengunggah file revisi menggunakan alur yang jelas.

Jangan menimpa file yang sudah ada sebelum file pengganti berhasil divalidasi dan disimpan.

## D. Download

Gunakan endpoint download yang:

1. Memeriksa apakah pengguna login jika diperlukan.
2. Mengambil informasi dokumen dari database.
3. Memeriksa tingkat privasi dokumen.
4. Memastikan pengguna memiliki hak akses.
5. Mengambil file dari lokasi penyimpanan yang benar.
6. Mengirim file menggunakan header HTTP yang sesuai.

Jangan menampilkan path penyimpanan internal.

Jangan mengandalkan URL langsung ke direktori upload.

Gunakan header Content-Disposition yang aman dan cegah manipulasi nama file.

## E. Hapus dokumen

Pemilik hanya boleh menghapus dokumennya sendiri.

Untuk versi pertama, penghapusan boleh langsung dilakukan jika implementasinya sederhana dan aman.

Namun, lebih baik jika tersedia soft delete menggunakan deleted_at, sehingga dokumen dapat dipulihkan sebelum dihapus permanen.

Jika soft delete dibuat, pastikan dokumen yang sudah dihapus tidak dapat diakses melalui endpoint download biasa.

---

# 7. STRUKTUR DATABASE

Buat file SQL yang dapat langsung diimpor melalui phpMyAdmin.

Nama database:

classvault_db

Gunakan tiga tabel inti.

## Tabel users

Kolom minimal:

- id: primary key.
- name.
- institution.
- nim.
- program_study.
- email, nullable.
- password_hash.
- role.
- status akun.
- created_at.
- updated_at.

Gunakan kombinasi institusi dan NIM yang dinormalisasi sebagai identitas unik.

Role hanya student dan admin.

Status akun dapat berupa active atau disabled.

Pastikan relasi dan indeks database sesuai kebutuhan.

## Tabel categories

Kolom minimal:

- id.
- name.
- created_at.

Masukkan kategori awal menggunakan seed SQL.

## Tabel documents

Kolom minimal:

- id.
- owner_id.
- category_id.
- title.
- description.
- original_filename.
- storage_filename.
- mime_type.
- file_size.
- visibility.
- created_at.
- updated_at.
- deleted_at, nullable.

Buat foreign key yang sesuai.

Gunakan indeks untuk owner_id, category_id, visibility, created_at, dan kolom pencarian yang relevan.

Pastikan data dokumen yang dimiliki pengguna lain tidak dapat diakses hanya melalui perubahan ID.

Jangan menyimpan isi file biner langsung di database.

Jangan menyimpan password asli.

Buat file:

database/classvault.sql

Pastikan SQL dapat diimpor melalui phpMyAdmin.

---

# 8. STRUKTUR FOLDER

Gunakan struktur modular yang mudah dipahami:

classvault/
├── app/
│   ├── config/
│   ├── controllers/
│   ├── models/
│   ├── services/
│   ├── middleware/
│   ├── helpers/
│   └── views/
├── public/
│   ├── index.php
│   ├── .htaccess
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
├── database/
│   └── classvault.sql
├── storage/
│   └── uploads/
├── .env.example
├── .gitignore
└── README.md

Boleh menyesuaikan struktur jika ada alasan teknis yang jelas, tetapi pertahankan pemisahan konfigurasi, business logic, tampilan, dan penyimpanan file.

Gunakan satu front controller jika memungkinkan.

Jangan menaruh seluruh aplikasi di satu file PHP.

Jangan menggandakan logika autentikasi atau otorisasi pada banyak file tanpa alasan.

---

# 9. KEAMANAN APLIKASI

Keamanan adalah persyaratan inti.

Terapkan:

1. Prepared statements PDO untuk query database.
2. password_hash() dan password_verify().
3. Session ID regeneration setelah login.
4. Cookie session dengan HttpOnly dan SameSite.
5. Secure cookie ketika menggunakan HTTPS.
6. Perlindungan CSRF pada seluruh form yang mengubah data.
7. Output escaping untuk mencegah XSS.
8. Validasi input sisi server.
9. Pembatasan percobaan login.
10. Pemeriksaan kepemilikan dokumen di backend.
11. Pemeriksaan hak akses setiap kali file diunduh.
12. Validasi file upload.
13. Penggunaan nama file internal acak.
14. Perlindungan terhadap akses langsung ke direktori sensitif.
15. Penanganan error yang tidak membocorkan password, kredensial database, path internal, atau stack trace kepada pengguna.
16. Konfigurasi database yang terpisah dari source code.
17. Pembatasan akses ke file konfigurasi.
18. Logout yang mengakhiri session dengan benar.

Utamakan penyimpanan file di luar document root.

Jika lingkungan hosting tidak memungkinkan hal tersebut, gunakan pengaturan akses yang sesuai dan endpoint download terproteksi.

Jangan pernah mengeksekusi file yang diunggah pengguna.

Jangan mengaktifkan directory listing.

Jangan menganggap pemeriksaan frontend sebagai mekanisme keamanan.

Jangan menyertakan password, API key, atau kredensial asli dalam repository.

Jangan mengklaim aplikasi aman sebelum melakukan pengujian.

---

# 10. DESAIN UI/UX

Buat desain modern, minimalis, dan profesional.

Inspirasi pengalaman pengguna boleh berasal dari Google Drive, tetapi jangan menyalin logo, identitas merek, atau tampilan persisnya.

Palet warna:

- Navy: #172033
- Primary blue: #4568F5
- Background: #F6F8FC
- Card: #FFFFFF
- Primary text: #202938
- Secondary text: #778195

Gunakan font Inter atau sans-serif yang sejenis.

Gunakan:

- Sidebar di desktop.
- Navigasi yang nyaman di HP.
- Search bar.
- Tombol upload yang mudah ditemukan.
- Tabel dokumen.
- Kartu statistik sederhana.
- Modal konfirmasi.
- Toast notification.
- Empty state yang informatif.
- Loading state bila diperlukan.
- Pesan error yang jelas.

Hindari:

- Animasi berlebihan.
- Terlalu banyak gradien.
- Tombol yang tidak berfungsi.
- Data dummy permanen setelah aplikasi siap digunakan.
- Layout yang terlalu padat.
- Menu yang tidak memiliki fungsi nyata.

Pastikan layout responsif untuk laptop, tablet, dan smartphone.

Gunakan HTML semantik dan pastikan tombol serta form dapat digunakan dengan keyboard.

---

# 11. HALAMAN YANG WAJIB DIBUAT

## Halaman publik

1. Beranda.
2. Jelajahi dokumen publik.
3. Detail dokumen publik.
4. Registrasi.
5. Login.

## Halaman mahasiswa

1. Dashboard.
2. File Saya.
3. Unggah File.
4. Detail Dokumen.
5. Edit Metadata Dokumen.
6. File Dibagikan.
7. Profil dan Pengaturan.
8. Ubah Password.

## Halaman admin

1. Dashboard Admin.
2. Daftar Pengguna.
3. Detail Pengguna.
4. Aktifkan atau nonaktifkan akun.
5. Logout.

Jangan membuat halaman yang hanya berupa placeholder jika halaman tersebut masuk ke daftar fitur yang dinyatakan selesai.

---

# 12. PENGATURAN KONFIGURASI

Pisahkan konfigurasi dari source code.

Sediakan .env.example dengan variabel yang diperlukan, misalnya:

APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/classvault/public

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=classvault_db
DB_USER=root
DB_PASS=

Jangan berasumsi bahwa PHP dapat membaca file .env secara otomatis.

Jika menggunakan library untuk membaca .env, jelaskan cara memasang dependency tersebut. Jika tidak menggunakan library, buat mekanisme konfigurasi yang jelas dan aman.

Jangan mengaktifkan debug mode ketika aplikasi sudah dipublikasikan.

Jangan menyimpan kredensial asli ke GitHub.

---

# 13. PENGUJIAN WAJIB

Buat dan jalankan pengujian untuk skenario berikut.

## Autentikasi

- Registrasi berhasil.
- Registrasi ditolak jika kombinasi institusi dan NIM sudah digunakan.
- Login berhasil dengan kredensial benar.
- Login gagal dengan password salah.
- Pengguna nonaktif tidak dapat login.
- Session berakhir setelah logout.
- Pengguna anonim tidak dapat membuka dashboard privat.

## Privasi

- Pemilik dapat melihat dokumennya sendiri.
- Pengguna lain tidak dapat melihat dokumen private.
- Pengguna login dapat melihat dokumen authenticated.
- Pengunjung anonim tidak dapat melihat dokumen authenticated.
- Pengunjung anonim dapat melihat dokumen public.
- Pengguna tidak dapat mengunduh dokumen tanpa hak akses.
- Mengubah ID dokumen pada URL tidak dapat melewati pemeriksaan izin.

## Pengelolaan dokumen

- Upload file valid berhasil.
- Upload file terlalu besar ditolak.
- Upload ekstensi yang tidak diizinkan ditolak.
- File dengan konten yang tidak sesuai dengan ekstensi ditolak jika dapat dideteksi.
- Edit metadata berhasil.
- Pemilik dapat mengubah privasi dokumennya sendiri.
- Pengguna lain tidak dapat mengubah metadata dokumen tersebut.
- Penghapusan dokumen bekerja sesuai desain.
- File yang sudah dihapus tidak dapat diunduh melalui endpoint biasa.

## Database dan deployment

- SQL dapat diimpor melalui phpMyAdmin.
- Koneksi database menggunakan konfigurasi.
- Aplikasi berjalan di XAMPP.
- Path aplikasi tidak diasumsikan selalu berada di root domain.
- Error produksi tidak membocorkan informasi internal.

Jika ditemukan kegagalan, perbaiki sebelum menyatakan fitur selesai.

Jika pengujian tertentu tidak dapat dijalankan, jelaskan secara jujur dan berikan langkah pengujian manual.

---

# 14. TAHAPAN PENGERJAAN

Kerjakan dalam tahap berikut.

## Tahap 1: Perencanaan

- Periksa lingkungan kerja.
- Buat struktur folder.
- Buat dokumentasi arsitektur singkat.
- Tentukan konfigurasi aplikasi.
- Buat skema database.

Jangan langsung membuat seluruh fitur.

## Tahap 2: Fondasi aplikasi

- Buat koneksi database.
- Buat routing atau front controller.
- Buat layout utama.
- Buat halaman beranda.
- Pastikan aplikasi berjalan melalui XAMPP.

## Tahap 3: Autentikasi

- Registrasi.
- Login.
- Logout.
- Session.
- Middleware autentikasi.
- Pembuatan admin awal dengan cara aman.

Uji seluruh proses autentikasi.

## Tahap 4: Pengelolaan dokumen

- Upload.
- Daftar dokumen.
- Detail.
- Edit metadata.
- Pengaturan privasi.
- Download.
- Hapus.

Uji pemeriksaan kepemilikan dan privasi untuk setiap operasi.

## Tahap 5: Pencarian dan UI

- Pencarian.
- Filter kategori.
- Pengurutan.
- Pagination.
- Dashboard.
- Tampilan responsif.
- Notifikasi.
- Empty state.

## Tahap 6: Admin

- Daftar pengguna.
- Pencarian pengguna.
- Detail pengguna.
- Menonaktifkan dan mengaktifkan akun.

Pastikan admin tidak otomatis dapat membaca dokumen private.

## Tahap 7: Audit dan deployment

- Jalankan pengujian.
- Periksa validasi file.
- Periksa hak akses.
- Periksa CSRF dan XSS.
- Buat README.
- Buat panduan deployment.
- Siapkan konfigurasi produksi.
- Jelaskan kebutuhan hosting dan penyimpanan file.

Jangan menyatakan proyek selesai hanya karena halaman utama terlihat bagus.

---

# 15. DOKUMENTASI

Buat README.md berbahasa Indonesia yang menjelaskan:

1. Deskripsi proyek.
2. Fitur.
3. Teknologi.
4. Persyaratan sistem.
5. Cara memasang XAMPP.
6. Cara mengimpor database melalui phpMyAdmin.
7. Cara mengatur konfigurasi.
8. Cara menjalankan aplikasi.
9. Cara membuat akun admin.
10. Cara menguji upload dan download.
11. Cara menguji hak akses.
12. Struktur folder.
13. Persiapan deployment.
14. Keterbatasan versi pertama.
15. Panduan backup database dan dokumen.

Jangan mengasumsikan bahwa pembaca sudah memahami struktur aplikasi PHP.

---

# 16. BATASAN PROYEK

Jangan menambahkan fitur berikut pada versi pertama:

- Penilaian tugas.
- Absensi.
- Deadline pengumpulan.
- Kelas virtual.
- Chat.
- Forum diskusi.
- Sistem revisi kolaboratif.
- Editor PDF atau Word di browser.
- Berbagi dokumen berdasarkan grup.
- Berbagi dokumen kepada pengguna tertentu.
- Sinkronisasi otomatis dengan Google Drive.
- Pembayaran.
- Paket langganan.
- Aplikasi mobile native.

Fokus pada penyimpanan, pengelolaan, privasi, dan berbagi dokumen.

---

# 17. CARA BEKERJA DENGAN SAYA

Saya masih belajar mengembangkan website dan menggunakan Antigravity sebagai AI coding assistant.

Karena itu:

- Jelaskan perubahan secara singkat.
- Gunakan struktur kode yang mudah dipahami.
- Jangan mengubah banyak file yang tidak berhubungan tanpa alasan.
- Sebelum menambahkan dependency baru, jelaskan kegunaannya.
- Jangan membuat fitur tambahan di luar spesifikasi tanpa persetujuan.
- Jika ada keputusan teknis yang belum ditentukan, pilih pendekatan sederhana dan aman.
- Jika terjadi error, diagnosis penyebabnya terlebih dahulu, kemudian perbaiki.
- Setelah setiap tahap, sebutkan file yang dibuat atau diubah dan cara mengujinya.
- Jangan menyatakan pekerjaan selesai jika fitur inti belum diuji.

MULAI DARI TAHAP 1.

Periksa lingkungan kerja dan buat rencana implementasi serta struktur proyek. Setelah itu, lanjutkan ke fondasi aplikasi dan kerjakan secara bertahap sampai fitur inti selesai, diuji, dan terdokumentasi.