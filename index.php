<?php
$app = require __DIR__ . '/bootstrap/app.php';

try {
    $app->make('console')->run();
} catch (Exception $e) {
    print $e->getMessage();
}