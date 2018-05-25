<?php

require_once "vendor/autoload.php";
require_once "FamilyPhotoSorter.php";

$FPS = new \FamilyPhotoSorter\FamilyPhotoSorter();

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('/home/cedric/Documents/Document Exemple/photo'));

$files = array();

foreach ($rii as $file) {

    if ($file->isDir()){
        continue;
    }
    $files[] = $file->getPathname();
}
foreach ($files as $file){
    $FPS->handleAFile($file);
}
