<?php

use Viewi\App;

$config = require __DIR__ . '/config.php';
include __DIR__ . '/routes.php';
App::init($config);
