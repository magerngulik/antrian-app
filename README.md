<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Hai semua nya yang membaca ini, ini merupakan aplikasi antrian pelangan seperti di bank, rumah sakit dan sebagainya, ada beberapa fiture utama dari api ini yang bisa anda kombinasikan

## Fiture

### Authentification
- login 
    pada login anda akan mendapatkan sebuah data user dan assignment id yang memiliki fungsi utama untuk pik role, sederhana nya ini akan menentukan karyawan anda di mana dia akan bertugas, bisa di teler atau costumer servies,
- register
- logout

### Karyawan/user 
- all role today
menyiapkan role atau posisi kerja, ini akan berubah setiap hari, jadi pastikan karyawan anda login dan memilih tempat yang benar.
- pick role
karyawan mengirimkan id user dan id role yang sudah di pilih.
- pick queue
bagian ini anda akan memulai tugas berdasarkan queue yang sudan pelangangan/costumer pilih.
- end queue
bagian ini akan berfungsi setelah karyawan melakukan pelayanan terhadap costumer dan akan mendapatkan hak akses untuk melakukan pick queue.


### Pelangan/costumer
- all role queue
ini merupakan bagian yang di sediakan, bagian ini akan menampilkan list/ layanan apa saja yang tersedia untuk pelangan seperti costumer services, teler dan sebaginya,
- get queue 
bagian ini setelah user memilih role/layanan maka user akan mendapatkan nomor antrian dengan unique id pada hari tersebut, yang akan tertera di user merupaan code seperti A001, B001 dan sebagai nya

## Dokumentasi
untuk dokumentasi anda bisa mengakses di *baseUrl/api/documentation/*, dokumentasi ini di buat dengan menggunakan swagger, untuk semua penjelasan sudah saya sertakan di dalam deskripsi, *selamat mencoba ya*   