<?php

$autoloader = require __DIR__ . '/../src/Console/autoload.php';

if (!$autoloader()) {
    die('uh-oh');
}

$app = new Stratedge\Engine\Console\Application();
$app->run();