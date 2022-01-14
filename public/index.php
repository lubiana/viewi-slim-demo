<?php

require_once __DIR__ . '/../vendor/autoload.php';

/** @var \Slim\App $app */
$app = (require __DIR__ . '/../src/SlimViewi.php')();

$app->run();

