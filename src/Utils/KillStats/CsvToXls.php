<?php

namespace App\Utils\KillStats;

use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ExceptionAlias;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Symfony\Component\HttpFoundation\File\File;

class CsvToXls
{

    /**
     * @param File $file
     * @return string
     * @throws ExceptionAlias
     * @throws Exception
     */
    public function getXls(File $file)
    {
        $spreadsheet = new Spreadsheet();
        $reader      = new Csv();

        /* Set CSV parsing options */
        $reader->setDelimiter(',');
        $reader->setEnclosure('"');
        $reader->setSheetIndex(0);

        /* Load a CSV file and save as a XLS */
        $spreadsheet = $reader->load($file->getPathname());
        $writer      = new Xls($spreadsheet);
        $writer->save(sprintf(ProfilesToCsv::UPLOAD_DIR, ProfilesToCsv::UPLOAD_XLS));

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        return sprintf(ProfilesToCsv::UPLOAD_DIR, ProfilesToCsv::UPLOAD_XLS);
    }
}