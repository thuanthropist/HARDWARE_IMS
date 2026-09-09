<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | mysqldump binary
    |--------------------------------------------------------------------------
    |
    | Path to the mysqldump executable used by the manual database backup
    | trigger in Admin Settings. Defaults to "mysqldump" (resolved via PATH),
    | override in .env if it isn't on PATH — e.g. a XAMPP install's
    | mysql/bin/mysqldump.exe.
    |
    */

    'mysqldump_path' => env('MYSQLDUMP_PATH', 'mysqldump'),

];
