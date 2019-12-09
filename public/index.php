<?php

use App\Exceptions\FileWrapperException;
use App\Services\FileWrapper;

require_once __DIR__ . '/../vendor/autoload.php';

$wrapper = new FileWrapper(__DIR__);
try {
    $wrapper->createFile('hello.txt','Hello World');
    $files = $wrapper->scan();
} catch (FileWrapperException $e) {
    echo $e->getMessage();
}
