<?php
$dir = __DIR__ . '/resources/views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$count = 0;
foreach ($iterator as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;
    $path = $file->getPathname();
    if (!str_ends_with($path, '.blade.php')) continue;

    $content = file_get_contents($path);
    $original = $content;

    $content = str_replace("layouts.palantir", "layouts.theme", $content);

    if ($original !== $content) {
        file_put_contents($path, $content);
        $count++;
    }
}
echo "Replaced in $count files.\n";

$oldPath = $dir . '/layouts/palantir.blade.php';
$newPath = $dir . '/layouts/theme.blade.php';

if (file_exists($oldPath)) {
    rename($oldPath, $newPath);
    echo "Renamed palantir.blade.php to theme.blade.php\n";
} else {
    echo "palantir.blade.php not found.\n";
}
