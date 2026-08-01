<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$users = App\User::all();
foreach($users as $u) {
    echo $u->id . ' - ' . ($u->photo ?? 'null') . "\n";
}
