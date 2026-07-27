<?php

$files = glob(__DIR__ . '/database/factories/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Replace unique()->word() with word() . '-' . uniqid()
    // but a regex might be better:
    $content = preg_replace('/->unique\(\)->([a-zA-Z0-9_]+)\(\)/', '->$1() . \'-\' . uniqid()', $content);
    
    file_put_contents($file, $content);
}
echo "Fixed factories.\n";