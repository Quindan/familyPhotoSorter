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
        $new = dirname($file) . '/' . gmdate('Y/m/', $time) . $kidsNameAndAge . basename($file);

        if (file_exists($new)) {
            echo ('Aborting, ' . $new . ' exists!');
            return;
        }
        mkdir(dirname($new), 0700,true);

        rename($file, $new);
    }

    /**
     * @param $time
     * @return string
     */
    private function outputKidsNameAndAge($time)
    {

        return "";
    }

    private function readConfig()
    {
        $yaml = file_get_contents("config.yml");
        $config = Yaml::parse($yaml);
        $this->kids = $config["kids"];
    }
}