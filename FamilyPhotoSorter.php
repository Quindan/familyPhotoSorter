<?php

namespace FamilyPhotoSorter;


use DateTime;
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
     * @param $timePreviouslyGuessedInFolder
     * @return mixed|null|void
     */
    function handleAFile($file, $timePreviouslyGuessedInFolder)
    {
        sprintf('Reading file "%s".', $file);
        try {
           $time = $this->getTimeFromMetaData($file);
        }catch(\Exception $e){
           $time = null;

        }
        if ($time === null ){

          $time = $this->getTimeByFileName($file);

        }

        if ($time === null){
            $time = $timePreviouslyGuessedInFolder;
        }


        $kidsNameAndAge = $this->outputKidsNameAndAge($time);

        $new = $this->photoSource . '/' . gmdate('Y/m', $time) . $kidsNameAndAge . '/' . basename($file);
        if (file_exists($new)) {
//            echo('skipping, ' . $new . ' exists! can\'t rename from '.$file . PHP_EOL);
            return;
        }
        if (!is_dir(dirname($new))) {
            mkdir(dirname($new), 0700, true);
        }

        rename($file, $new);
        echo '.';
        return $time;
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
        if ($time < 1384297200){
            return $output;
        }
        foreach ($this->kids as $kid) {
            $birthDate = new DateTime($kid['dateOfBirth']);

            if ($birthDate->getTimestamp() < $time){
                $output .= ' '.$kid['name'] . ' '. $this->ageAtTime($kid['dateOfBirth'], $time) ;
            }
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

    /**
     * @param $file
     * @return mixed
     * @throws \lsolesen\pel\PelInvalidArgumentException
     * @throws \lsolesen\pel\PelJpegInvalidMarkerException
     * @throws \Exception
     */
    private function getTimeFromMetaData($file)
    {

        $data = new PelDataWindow(file_get_contents($file));
        if (PelJpeg::isValid($data)) {
            $jpeg = new PelJpeg();
            $jpeg->load($data);
            $app1 = $jpeg->getExif();
            if ($app1 == null) {
                throw (new \Exception(sprintf('Skipping %s because no APP1 section was found.', $file)));
            }

            $tiff = $app1->getTiff();
        } elseif (PelTiff::isValid($data)) {
            $tiff = new PelTiff($data);
        } else {
            throw (new \Exception(sprintf('Unrecognized image format!')));
        }

        $ifd0 = $tiff->getIfd();
        $entry = $ifd0->getEntry(PelTag::DATE_TIME);

        if ($entry == null) {
            throw (new \Exception(sprintf('Skipping %s because no DATE_TIME tag was found.', $file)));
        }

        return $entry->getValue();
    }

    private function getTimeByFileName($file)
    {
        var_dump($file);
        return null;
    }
}