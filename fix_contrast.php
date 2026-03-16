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

    $replaces = [
        'text-white/10' => 'text-white/30',
        'text-white/20' => 'text-white/40',
        'text-white/30' => 'text-white/50',
        'text-white/40' => 'text-white/70',
        'text-white/50' => 'text-white/80',
        'color:#333' => 'color:#777',
        'color:#444' => 'color:#999',
        'color:#555' => 'color:#bbb',
        'rgba(255,255,255,0.4)' => 'rgba(255,255,255,0.7)',
        'text-[10px]' => 'text-xs', // making microscopic text slightly bigger
        'font-size:0.55rem' => 'font-size:0.75rem',
        'font-size:0.58rem' => 'font-size:0.75rem',
        'font-size:0.6rem' => 'font-size:0.75rem',
        'font-size:0.65rem' => 'font-size:0.75rem',
    ];

    $content = str_replace(array_keys($replaces), array_values($replaces), $content);

    if ($original !== $content) {
        file_put_contents($path, $content);
        echo "Updated $path\n";
        $count++;
    }
}
echo "Total updated: $count\n";
