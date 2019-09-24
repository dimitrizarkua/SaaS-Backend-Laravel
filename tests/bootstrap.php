<?php

require_once __DIR__ . '/../vendor/autoload.php';

//Create application instance
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\App\Console\Kernel::class)->bootstrap();

//Run migrations
Artisan::call('migrate', ['--force' => true]);
