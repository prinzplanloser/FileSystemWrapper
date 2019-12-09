<?php

require_once __DIR__ . '/../src/Services/FileWrapper.php';


$wrapper = new FileWrapper(__DIR__);
$files = $wrapper->scan();