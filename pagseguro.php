<?php

require_once 'vendor/autoload.php';

use LibreCode\OfxHandler\PagSeguro;

$parser = new PagSeguro($argv[2]);

$json = file_get_contents($argv[1]);
$json = json_decode($json);
$ofx = $parser->parseJson($json);

echo $ofx;