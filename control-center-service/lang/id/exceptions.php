<?php

return [
    //* Request Exceptions
    'bad_request' => 'Permintaan buruk',
    'unauthorized' => 'Tidak sah',
    'forbidden' => 'Akses ke sumber daya dilarang',
    'not_found' => 'Sumber daya yang diminta tidak ditemukan',
    'unprocessable_content' => 'Konten tidak dapat diproses',
    'unexpected_error' => 'kesalahan tak terduga',

    //* Auth Exceptions
    'invalid_token' => 'Tanda Tangan Token tidak dapat diverifikasi',
    'account_blocked' => 'Akun Anda telah diblokir oleh administrator.',
    'account_not_found' => 'Akun tidak ditemukan',
    'invalid_credential' => 'Kredensial tidak valid',
    'email_not_verified' => 'Email belum diverifikasi',
    'token_not_provided' => 'Token tidak ditemukan',
    'expired_token' => 'Token telah kedaluwarsa',
    'could_not_create_token' => 'Token tidak dapat dibuat',
    'token_replace' => 'Sesi berakhir. Anda telah masuk dari perangkat lain',
    'banned_token' => 'Token telah masuk daftar hitam',

    //* Register & Verify
    'link_verification_invalid' => 'Link email verifikasi tidak valid',
    'email_already_verified' => 'Email sudah diverifikasi',

    'user_has_no_active_token' => 'Pengguna tidak memiliki token aktif',
    'token_already_banned' => 'Token sudah masuk daftar hitam',
    'unbanned_token_not_found' => 'Token saat ini tidak masuk daftar hitam',
    'too_many_requests' => 'Terlalu banyak permintaan',
    'unexpected_error' => 'Terjadi kesalahan tak terduga',

    //* Internal Service Exception
    'service_unavailable' => 'Layanan sementara tidak tersedia',
    'invalid_service_response' => 'Respon tidak valid diterima dari internal service',
];
