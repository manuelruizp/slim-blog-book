<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$checks = 0;

function snapshotFiles(string $directory): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($directory) + 1));
        if (str_starts_with($relative, 'vendor/')
            || preg_match('/\.(?:sqlite(?:-.*)?|sqlite3|db|log)$/', $relative)
            || str_starts_with(basename($relative), 'sess_')) {
            continue;
        }
        $files[$relative] = hash_file('sha256', $file->getPathname());
    }
    ksort($files);
    return $files;
}

try {
    for ($chapter = 2; $chapter <= 10; $chapter++) {
        $previous = sprintf('%s/chapter-%02d/end', $root, $chapter - 1);
        $current = sprintf('%s/chapter-%02d/start', $root, $chapter);
        if (snapshotFiles($previous) !== snapshotFiles($current)) {
            throw new RuntimeException('La continuidad falla antes del capítulo ' . $chapter);
        }
        $checks++;
    }

    $phpFiles = 0;
    for ($chapter = 1; $chapter <= 10; $chapter++) {
        foreach (['start', 'end'] as $phase) {
            $directory = sprintf('%s/chapter-%02d/%s', $root, $chapter, $phase);
            foreach (snapshotFiles($directory) as $relative => $hash) {
                if (pathinfo($relative, PATHINFO_EXTENSION) !== 'php') {
                    continue;
                }
                $process = proc_open(
                    [PHP_BINARY, '-l', $directory . '/' . $relative],
                    [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                    $pipes
                );
                if (!is_resource($process)) {
                    throw new RuntimeException('No se pudo iniciar el comprobador de sintaxis.');
                }
                $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                if (proc_close($process) !== 0) {
                    throw new RuntimeException($output);
                }
                $phpFiles++;
            }
        }
    }

    echo $checks . ' transiciones idénticas y ' . $phpFiles
        . ' archivos PHP sin errores de sintaxis.' . PHP_EOL;
    echo 'Esta verificación no ejecuta las pruebas HTTP ni las de base de datos.' . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
