<?php

return [
    'email' => 'Email',
    'password' => 'Kata sandi',
    'username' => 'Nama pengguna',
    'is_active' => 'status aktif',

    /**
     * Specific
    */
    //* auth user
    'auth_user.auth_user_email' => 'email pengguna',
    'auth_user.auth_user_username' => 'nama pengguna',
    'auth_user.auth_user_password' => 'kata sandi pengguna',
    'auth_user.auth_user_role_id' => 'peran pengguna',
    'auth_user.auth_user_is_admin' => 'admin pengguna',
    'auth_user.auth_user_is_status' => 'status pengguna',

    //* company
    'company.company_name' => 'nama perusahaan',
    'company.company_address' => 'alamat perusahaan',
    'company.company_village_id' => 'desa perusahaan',
    'company.company_zip_code' => 'kode pos perusahaan',
    'company.company_phone' => 'telepon perusahaan',
    'company.company_fax' => 'fax perusahaan',
    'company.company_website' => 'website perusahaan',
    'company.company_email' => 'email perusahaan',

    //* user
    // 'user.user_auth_user_id' => 'id auth pengguna',
    'user.user_first_name' => 'nama depan pengguna',
    'user.user_last_name' => 'nama belakang pengguna',
    'user.user_gender' => 'jenis kelamin pengguna',
    'user.user_address' => 'alamat pengguna',
    'user.user_village_id' => 'desa pengguna',
    'user.user_zip_code' => 'kode pos pengguna',
    'user.user_phone' => 'no. telepon pengguna',

    //* Auth Role
    'auth_role_slug' => 'slug peran',
    'auth_role_name' => 'nama peran',

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

    //* Auth Role Permissions
    'auth_role_permissions' => 'daftar izin peran',
    'auth_role_permissions_permission_id' => 'ID izin',
    'auth_role_permissions_parameter' => 'parameter izin',
];
