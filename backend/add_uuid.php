<?php

$files = glob(__DIR__ . '/app/Models/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    if (strpos($content, 'HasUuids') !== false) {
        continue;
    }

    // Add import
    $import = "use Illuminate\\Database\\Eloquent\\Concerns\\HasUuids;\n";
    $searchImport = "use Illuminate\\Database\\Eloquent\\Model;";
    
    if (strpos($content, $searchImport) !== false) {
        $content = str_replace($searchImport, $searchImport . "\n" . $import, $content);
    } else {
        // Fallback for models without it (shouldn't happen but just in case)
        $content = preg_replace('/namespace App\\\\Models;/', "namespace App\\Models;\n\n" . $import, $content);
    }

    // Add trait inside class
    // Look for use HasFactory; or use HasApiTokens, HasFactory, Notifiable;
    if (preg_match('/use ([a-zA-Z0-9_, ]+);/', $content, $matches)) {
        if (strpos($matches[0], 'HasUuids') === false) {
            $newTrait = str_replace($matches[1], $matches[1] . ', HasUuids', $matches[0]);
            $content = str_replace($matches[0], $newTrait, $content);
        }
    } else {
        // Add use HasUuids; right after class declaration
        $content = preg_replace('/class ([a-zA-Z0-9_]+) extends Model\n\{/', "class $1 extends Model\n{\n    use HasUuids;\n", $content);
    }
    
    file_put_contents($file, $content);
    echo "Added HasUuids to " . basename($file) . "\n";
}