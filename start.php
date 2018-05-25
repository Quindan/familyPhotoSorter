<?php

require_once "vendor/autoload.php";
require_once "FamilyPhotoSorter.php";

new \FamilyPhotoSorter\FamilyPhotoSorter();

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('/home/cedric/Documents/Document Exemple/photo'));

$files = array();

foreach ($rii as $file) {

    if ($file->isDir()){
        continue;
    }

    $files[] = $file->getPathname();

}



var_dump($files);
