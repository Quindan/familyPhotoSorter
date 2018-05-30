<?php

namespace FamilyPhotoSorter;


use lsolesen\pel\PelDataWindow;
use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelTag;
use lsolesen\pel\PelTiff;
use Symfony\Component\Yaml\Yaml;


class FamilyPhotoSorter
{
    private $kids = [];
    private $photoSource = null;


    public function __construct()
    {
        $this->readConfig();
    }

    /**
     * @param $file
     * @throws \lsolesen\pel\PelInvalidArgumentException
     * @throws \lsolesen\pel\PelJpegInvalidMarkerException
     */
    function handleAFile($file)
    {
        sprintf('Reading file "%s".', $file);
        $data = new PelDataWindow(file_get_contents($file));

        if (PelJpeg::isValid($data)) {
            $jpeg = new PelJpeg();
            $jpeg->load($data);
            $app1 = $jpeg->getExif();
            if ($app1 == null) {
                sprintf('Skipping %s because no APP1 section was found.', $file);
                return;
            }

            $tiff = $app1->getTiff();
        } elseif (PelTiff::isValid($data)) {
            $tiff = new PelTiff($data);
        } else {
            sprintf('Unrecognized image format! Skipping.');
            return;
        }

        $ifd0 = $tiff->getIfd();
        $entry = $ifd0->getEntry(PelTag::DATE_TIME);

        if ($entry == null) {
            sprintf('Skipping %s because no DATE_TIME tag was found.', $file);
            return;
        }

        $time = $entry->getValue();
        $kidsNameAndAge = $this->outputKidsNameAndAge($time);
        $new = $this->photoSource . '/' . gmdate('Y/m ', $time) . $kidsNameAndAge . '/' . basename($file);

        if (file_exists($new)) {
            echo('Aborting, ' . $new . ' exists!');
            return;
        }
        mkdir(dirname($new), 0700, true);

        rename($file, $new);
    }

    /**
     * @param $time
     * @return string
     */
    private function outputKidsNameAndAge($time)
    {
        $output = "";
        if (empty($this->kids)){
            return $output;
        }

        foreach ($this->kids as $kid) {
            $output .= ' '.$kid['name'] . ' '. $this->ageAtTime($kid['dateOfBirth'],$time) ;
        }
        $output = ' (' . $output . ' ) ';
        return $output;
    }

    private function readConfig()
    {
        $yaml = file_get_contents("config.yml");
        $config = Yaml::parse($yaml);
        $this->kids = $config["kids"];
        $this->photoSource = $config["photoSource"];
    }

    /**
     * @param $dateOfBirth
     * @param $time
     * @return int
     */
    private function ageAtTime($dateOfBirth, $time)
    {
        $from = new \DateTime($dateOfBirth);
        $to   = new \DateTime();
        $to->setTimestamp($time);

        $yearsPartOfTheAge = $from->diff($to)->y;
        $monthsPartOfTheAge = $from->diff($to)->m;
        if ($yearsPartOfTheAge < 1) {
            return $monthsPartOfTheAge . " mois ";
        }
        else if ($yearsPartOfTheAge === 1){
                return '1 an';

        } else{
            return $yearsPartOfTheAge . " ans ";
        }

    }
}