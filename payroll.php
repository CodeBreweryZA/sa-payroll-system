#!/usr/bin/env php
<?php

require_once __DIR__ . '/src/autoload.php';

use Payroll\Console\Application;

$app = new Application();
$app->run();