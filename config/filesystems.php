<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Storage Driver (generic STORAGE_* adapter)
    |--------------------------------------------------------------------------
    |
    | Driver storage aplikasi: local | oss | s3. Dipetakan ke disk pada
    | bagian "disks" di bawah ("local" dipetakan ke disk "public").
    |
    */

    'storage_driver' => env('STORAGE_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('STORAGE_ACCESS_KEY_ID'),
            'secret' => env('STORAGE_SECRET_ACCESS_KEY'),
            'region' => env('STORAGE_REGION', 'us-east-1'),
            'bucket' => env('STORAGE_BUCKET'),
            'url' => env('STORAGE_ENDPOINT'),
            'endpoint' => env('STORAGE_ENDPOINT'),
            'use_path_style_endpoint' => false,
            'throw' => false,
            'report' => false,
        ],

        'oss' => [
            'driver' => 's3',
            'key' => env('STORAGE_ACCESS_KEY_ID'),
            'secret' => env('STORAGE_SECRET_ACCESS_KEY'),
            'region' => env('STORAGE_REGION', ''),
            'bucket' => env('STORAGE_BUCKET'),
            'url' => env('STORAGE_ENDPOINT'),
            'endpoint' => env('STORAGE_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
