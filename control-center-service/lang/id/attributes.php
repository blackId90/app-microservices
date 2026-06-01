<?php

return [
     /**
     * General
     */
    'username' => 'nama pengguna',
    'password' => 'Kata sandi',
    'is_active' => 'status aktif',

     /**
     * Specific
     */
    //* auth user
    'auth_user.auth_user_email' => 'email pengguna',
    'auth_user.auth_user_username' => 'nama pengguna',
    'auth_user.auth_user_company_id' => 'perusahaan pengguna',
    'auth_user.auth_user_role_id' => 'peran pengguna',
    'auth_user.auth_user_is_admin' => 'admin pengguna',
    'auth_user.auth_user_is_status' => 'status pengguna',

    //* company
    'company.company_id' => 'ID perusahaan',
    'company.company_name' => 'Nama perusahaan',
    'company.company_address' => 'Alamat perusahaan',
    'company.company_village_id' => 'Desa perusahaan',
    'company.company_zip_code' => 'Kode pos perusahaan',
    'company.company_phone' => 'Telepon perusahaan',
    'company.company_fax' => 'Fax Perusahaan',
    'company.company_website' => 'Website perusahaan',
    'company.company_email' => 'Email perusahaan',

    //* user
    'user.user_auth_user_id' => 'ID auth pengguna',
    'user.user_first_name' => 'Nama depan pengguna',
    'user.user_last_name' => 'Nama belakang pengguna',
    'user.user_gender' => 'Jenis kelamin pengguna',
    'user.user_address' => 'Alamat pengguna',
    'user.user_village_id' => 'Desa pengguna',
    'user.user_zip_code' => 'Kode pos pengguna',
    'user.user_phone' => 'No. Telepon pengguna',

    //* Auth Role
    'auth_role_slug' => 'slug peran',
    'auth_role_name' => 'nama peran',

    //* Auth Role Permissions
    'auth_role_permissions' => 'daftar izin peran',
    'auth_role_permissions_permission_id' => 'ID izin',
    'auth_role_permissions_parameter' => 'parameter izin',

    //* Auth Permission
    'auth_permission_type' => 'tipe izin',
    'auth_permission_parent_permission_id' => 'induk izin',
    'auth_permission_slug' => 'slug izin',
    'auth_permission_title' => 'judul izin',
    'auth_permission_icon' => 'ikon izin',
    'auth_permission_color' => 'warna izin',
    'auth_permission_url' => 'url izin',
    'auth_permission_route' => 'rute izin',
    'auth_permission_target' => 'target izin',
    'auth_permission_order' => 'urutan izin',

    //* Currency
    'currency_code' => 'kode mata uang',
    'currency_name' => 'nama mata uang',
    'currency_symbol' => 'simbol mata uang',
    'currency_is_active' => 'status',

    //* Continent
    'continent_code' => 'kode benua',
    'continent_name' => 'nama benua',

    //* Country
    'country_code' => 'kode negara',
    'country_alpha_3' => 'kode negara alfa 3',
    'country_name' => 'nama negara',
    'country_capital' => 'ibu kota negara',
    'country_phone' => 'telepon negara',
    'country_continent_code' => 'kode benua negara',
    'country_currency_code' => 'kode mata uang negara',

    //* Language
    'language_code' => 'kode bahasa',
    'language_name' => 'nama bahasa',
];
