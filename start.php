<?php

require_once "vendor/autoload.php";
require_once "FamilyPhotoSorter.php";

$FPS = new \FamilyPhotoSorter\FamilyPhotoSorter();

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('/XXXX'));

$files = array();
$previousFolder = null;
$currentFolder = null;

$previousTime = null;
foreach ($rii as $file) {

    if ($file->isDir()){
        continue;
    }
    $currentFolder = $file->getPath();
    if ($currentFolder !== $previousFolder){
        $previousTime = null; // we change folder, we don't know what's the folder time
    }

    $previousTime = $FPS->handleAFile($file->getPathname(), $previousTime);
    $previousFolder = $file->getPath();
}
