<?php

require_once "vendor/autoload.php";
require_once "FamilyPhotoSorter.php";

//new \FamilyPhotoSorter\FamilyPhotoSorter();
$yaml = \Symfony\Component\Yaml\Yaml::dump(array(
    'photoSource' => '/home/me/Documents/',
    'kids' => array(
        array('name' => 'Kid1', 'dataOfBirth' => '2013-11-14'),
        array('name' => 'kid2', 'dataOfBirth' => '2015-08-10'),
    )
));
file_put_contents('./config.yml', $yaml);