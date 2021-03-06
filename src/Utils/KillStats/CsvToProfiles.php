<?php

namespace App\Utils\KillStats;

use App\Entity\Profile\Profile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CsvToProfiles
{
    const ID   = 'id';
    const NAME = 'name';

    /**
     * @var UploadedFile
     */
    private $csvFile;

    /**
     * @var array
     */
    private $dataArray;

    /**
     * @var array
     */
    private $validArray;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var array
     */
    private $body;

    /**
     * @param $csv
     * @return Profile[]
     */
    public function getProfilesFromCsv($csv)
    {
        $this->csvFile = $csv;
        $this->csvToArray();
        $this->sanitizeArray();
        $this->clearProfileNotValide();
        return $this->getProfileArray();
    }

    private function csvToArray()
    {
        $data          = array_map('str_getcsv', file($this->csvFile->getRealPath()));
        $this->headers = array_shift($data);
        $this->body    = $data;
    }


    private function sanitizeArray()
    {
        array_map(function ($profile) {
            $this->dataArray[] = array_combine($this->headers, $profile);
        }, $this->body);
    }

    private function clearProfileNotValide()
    {
        array_map(function ($profile) {
            if (!empty($profile[self::ID])) {
                $this->validArray[] = $profile;
            }
        }, $this->dataArray);
    }

    /**
     * @return Profile[]
     */
    private function getProfileArray()
    {
        return array_map(function ($data) {
            $profile = new Profile($data[self::ID]);
            if (array_key_exists(self::NAME, $data)) {
                $profile->setName($data[self::NAME]);
            }
            return $profile;
        }, $this->validArray);
    }


}