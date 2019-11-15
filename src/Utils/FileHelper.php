<?php

namespace App\Utils;

use App\Utils\KillStats\ProfilesToCsv;
use Symfony\Component\HttpFoundation\File\File;
use ZipArchive;
use App\Utils\KillStats\CsvToXls;

class FileHelper
{
    /**
     * @var ZipArchive
     */
    private $zip;

    /**
     * @var string
     */
    private $zipName;

    /**
     * @var string
     */
    private $now;

    private $xls;

    /**
     * @var CsvToXls
     */
    private $csvToXls;

    public function __construct(CsvToXls $csvToXls)
    {
        $this->zipName  = sprintf(ProfilesToCsv::UPLOAD_DIR, 'export-kills.zip');
        $this->now      = (new \DateTime("NOW"))->format('d-m-Y');
        $this->csvToXls = $csvToXls;
    }

    public function getZip($file)
    {
        $this->openZip();
        $this->zip->addFile($file, sprintf('export-kill-%s.csv', $this->now));
        $this->zip->addFile($this->getXls($file), sprintf('export-kill-%s.xls', $this->now));
        $this->zip->close();
        return $this->zipName;
    }

    private function openZip()
    {
        $this->zip = new ZipArchive;
        if (file_exists($this->zipName)) {
            $this->zip->open($this->zipName, ZipArchive::OVERWRITE);
            return;
        }
        $this->zip->open($this->zipName, ZipArchive::CREATE);
    }

    private function getXls($file)
    {
        $file = new File($file);
        return $this->csvToXls->getXls($file);
    }
}