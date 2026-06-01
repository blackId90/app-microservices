<?php

return [
    //* Request Exceptions
    'method_not_allowed' => 'Metode ini tidak didukung',
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
    'company_not_found' => 'Company tidak ditemukan',
    'role_inactive' => 'Peran anda tidak aktif. Silakan hubungi administrator.',
    'invalid_credential' => 'Kredensial tidak valid',
    'email_not_verified' => 'Email belum diverifikasi',
    'email_already_exists' => 'Email sudah terdaftar',
    'username_already_exists' => 'Username sudah terdaftar',
    'role_not_found' => 'Role tidak ditemukan',
    'token_not_provided' => 'Token tidak ditemukan',
    'expired_token' => 'Token telah kedaluwarsa',
    'could_not_create_token' => 'Token tidak dapat dibuat',
    'token_replace' => 'Sesi berakhir. Anda telah masuk dari perangkat lain',
    'banned_token' => 'Token telah masuk daftar hitam',

    'user_profile_mismatch' => 'Profil pengguna tidak sesuai dengan autentikasi',
    'company_mismatch' => 'Perusahaan tidak sesuai dengan autentikasi',
    'company_inactive' => 'Perusahaan Anda telah tidak aktif atau ditangguhkan. Silakan hubungi administrator.',
    'company_unpaid' => 'Perusahaan memiliki tagihan yang belum dibayar',
    'company_expired' => 'Masa berlaku tagihan perusahaan telah berakhir',
    'company_suspended' => 'Perusahaan ditangguhkan',
    'company_trial_expired' => 'Masa percobaan perusahaan telah berakhir',
    'company_paid_expired' => 'Langganan perusahaan telah berakhir',
    'company_billing_invalid' => 'Status penagihan perusahaan tidak valid',
    'company_config_auth_not_found' => 'Konfigurasi otentikasi perusahaan tidak ditemukan',
    'company_config_auth_missing' => 'Konfigurasi otentikasi perusahaan hilang',
    'invalid_signature' => 'Link verifikasi kedaluwarsa atau tanda tangan digital tidak valid',
    'link_verification_expired' => 'Link email verifikasi kadaluarsa',
    'link_verification_invalid' => 'Link email verifikasi tidak valid',
    'email_already_verified' => 'Email sudah diverifikasi',

    'user_has_no_active_token' => 'Pengguna tidak memiliki token aktif',
    'token_already_banned' => 'Token sudah masuk daftar hitam',
    'unbanned_token_not_found' => 'Token saat ini tidak masuk daftar hitam',
    'too_many_attempts' => 'Terlalu banyak percobaan',
    'too_many_requests' => 'Terlalu banyak permintaan',
    'unexpected_error' => 'Terjadi kesalahan tak terduga',

    //* Internal Service Exception
    'service_unavailable' => 'Layanan sementara tidak tersedia',
    'invalid_service_response' => 'Respon tidak valid diterima dari internal service',
];
