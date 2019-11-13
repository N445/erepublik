<?php

namespace App\Utils\KillStats;

use App\Entity\KillsStats\Plane;
use App\Entity\Profile\Profile;

class ProfilesToCsv
{
    const PROFILE_URL = "https://www.erepublik.com/en/citizen/profile/%s";
    const UPLOAD_DIR  = __DIR__ . '/../../../var/tmp/%s';
    const UPLOAD_CSV  = 'export-tmp.csv';
    const UPLOAD_XLS  = 'export-tmp.xls';

    private $killsTotal  = 0;

    private $monneyTotal = 0;

    /**
     * @param Profile[] $profiles
     * @return string
     */
    public function getCsvFromProfiles($profiles)
    {
        $this->sort($profiles);
        $path = sprintf(self::UPLOAD_DIR, self::UPLOAD_CSV);

        $fp = fopen($path, 'w');

        fputcsv($fp, $this->getHeaders());

        foreach ($profiles as $profile) {
            fputcsv($fp, $this->getProfileArray($profile));
        }

        $this->getFooter($fp, $profile);

        fclose($fp);
        return $path;
    }

    /**
     * @return array
     */
    private function getHeaders()
    {
        return [
            'Identifiant', 'Nom', 'Unité militaire', 'Nombre de kills', 'Argent à donner', 'url',
        ];
    }

    /**
     * @param Profile $profile
     * @return array
     */
    private function getProfileArray(Profile $profile)
    {
        /** @var Plane $lastStat */
        $lastStat          = $profile->getPlanes()->last();
        $this->killsTotal  = $this->killsTotal + ($lastStat ? $lastStat->getKills() : 0);
        $this->monneyTotal = $this->monneyTotal + ($lastStat ? $lastStat->getMoney() : 0);
        return [
            $profile->getIdentifier(),
            $profile->getName(),
            $profile->getUnitemilitaire()->getName(),
            $lastStat ? $lastStat->getKills() : 0,
            $lastStat ? $lastStat->getMoney() : 0,
            sprintf(self::PROFILE_URL, $profile->getIdentifier()),
        ];
    }

    private function getFooter(&$fp)
    {
        fputcsv($fp, [
            null, null, null,
            'Total Kills',
            'Total argent donné',
        ]);
        fputcsv($fp, [
            null, null, null,
            number_format($this->killsTotal, 0, ',', ' '),
            number_format($this->monneyTotal, 0, ',', ' '),
        ]);
    }

    private function sort(&$profiles)
    {
        $this->sortUn($profiles);
        $this->sortDeux($profiles);
    }

    private function sortUn(&$profiles)
    {
        usort($profiles, function (Profile $a, Profile $b) {
            return $a->getName() <=> $b->getName();
        });
    }

    private function sortDeux(&$profiles)
    {
        usort($profiles, function (Profile $a, Profile $b) {
            return $a->getUnitemilitaire()->getName() <=> $b->getUnitemilitaire()->getName();
        });
    }
}