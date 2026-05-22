<?php

/*
|--------------------------------------------------------------------------
| Documentation for this config:
|--------------------------------------------------------------------------
| Online  => http://unisharp.github.io/laravel-filemanager/config
| Offline => vendor/unisharp/laravel-filemanager/docs/config.md
*/

return [
    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    */

    'use_package_routes' => true,

    /*
    |--------------------------------------------------------------------------
    | Shared folder / Private folder
    |--------------------------------------------------------------------------
    |
    | If both options are set to false, then shared folder will be activated.
    |
    */

    'allow_private_folder' => true,

    // Flexible way to customize client folders accessibility.
    // To customize client folders, publish tag="lfm_handler"
    // Then, rewrite the userField function in App\Handler\ConfigHandler class
    // and set 'user_field' to App\Handler\ConfigHandler::class
    // Example: The private folder of user will be named as the user id.
    'private_folder_name' => UniSharp\LaravelFilemanager\Handlers\ConfigHandler::class,

    'allow_shared_folder' => false,

    'shared_folder_name' => 'shares',

    /*
    |--------------------------------------------------------------------------
    | Folder Names
    |--------------------------------------------------------------------------
    */

    'folder_categories' => [
        'file' => [
            'folder_name' => 'files',
            'startup_view' => 'grid',
            'max_size' => 50000, // size in KB
            'valid_mime' => [
                'image/jpeg',
                'image/pjpeg',
                'image/png',
                'image/gif',
                'image/svg+xml',
            ],
        ],
        'image' => [
            'folder_name' => 'photos',
            'startup_view' => 'list',
            'max_size' => 50000, // size in KB
            'valid_mime' => [
                'image/jpeg',
                'image/pjpeg',
                'image/png',
                'image/gif',
                'image/svg+xml',
                'application/pdf',
                'text/plain',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    'paginator' => [
        'perPage' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload / Validation
    |--------------------------------------------------------------------------
    */

    'disk' => 'public',

    'rename_file' => false,

    'alphanumeric_filename' => false,

    'alphanumeric_directory' => false,

    'should_validate_size' => true,

    'should_validate_mime' => true,

    // Behavior on files with identical names
    // Setting it to true causes the old file to be replaced with the new one
    // Setting it to false shows `error-file-exist` error and stops upload
    'over_write_on_duplicate' => false,

    /*
    |--------------------------------------------------------------------------
    | Thumbnail
    |--------------------------------------------------------------------------
    */

    // If true, image thumbnails will be created during upload
    'should_create_thumbnails' => true,

    'thumb_folder_name' => 'thumbs',

    // Create thumbnails automatically only for listed types
    'raster_mimetypes' => [
        'image/jpeg',
        'image/pjpeg',
        'image/png',
    ],

    'thumb_img_width' => 200, // in px

    'thumb_img_height' => 200, // in px

    /*
    |--------------------------------------------------------------------------
    | File Extension Information
    |--------------------------------------------------------------------------
    */

    'file_type_array' => [
        'pdf' => 'Adobe Acrobat',
        'doc' => 'Microsoft Word',
        'docx' => 'Microsoft Word',
        'xls' => 'Microsoft Excel',
        'xlsx' => 'Microsoft Excel',
        'zip' => 'Archive',
        'gif' => 'GIF Image',
        'jpg' => 'JPEG Image',
        'jpeg' => 'JPEG Image',
        'png' => 'PNG Image',
        'ppt' => 'Microsoft PowerPoint',
        'pptx' => 'Microsoft PowerPoint',
    ],

    /*
    |--------------------------------------------------------------------------
    | php.ini override
    |--------------------------------------------------------------------------
    |
    | These values override your php.ini settings before uploading files
    | Set these to false to ignore and apply your php.ini settings
    |
    | Note: The 'upload_max_filesize' & 'post_max_size' directives are not supported.
    |
    */
    'php_ini_overrides' => [
        'memory_limit' => '256M',
    ],
];
