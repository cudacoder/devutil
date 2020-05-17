<?php
$srcRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'src');
$buildRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'build');
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcRoot, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY);

echo "Building DEVUtil...\n";

$phar = new Phar($buildRoot . DIRECTORY_SEPARATOR . 'devutil.phar', FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, 'devutil.phar');
$phar->buildFromIterator($iterator, $srcRoot);
$phar->setStub($phar->createDefaultStub('devutil'));

// Uncomment the following in case a .bat file is needed for Windows:
// file_put_contents('devutil.bat',"@ECHO OFF\nphp %~dp0devutil.phar %*");

exit("Build complete\n");