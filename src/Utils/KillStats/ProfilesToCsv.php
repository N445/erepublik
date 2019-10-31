<?php

namespace App\Utils\KillStats;

use App\Model\KillsStats\Profile;

class ProfilesToCsv
{
    /**
     * @param Profile[] $profiles
     * @return string
     */
    public function getCsvFromProfiles($profiles)
    {
        $this->sortByUm($profiles);
        $path = __DIR__ . '/../../../var/tmp/export-tmp.csv';

        $fp = fopen($path, 'w');

        fputcsv($fp, $this->getHeaders());

        foreach ($profiles as $profile) {
            fputcsv($fp, $this->getProfileArray($profile));
        }

        fclose($fp);
        return $path;
    }

    /**
     * @return array
     */
    private function getHeaders()
    {
        return [
            'Identifiant', 'Nom', 'Unité militaire', 'Nombre de kills', 'Argent à donner',
        ];
    }

    /**
     * @param Profile $profile
     * @return array
     */
    private function getProfileArray(Profile $profile)
    {
        return [
            $profile->getId(),
            $profile->getName(),
            $profile->getUmName(),
            $profile->getKills(),
            $profile->getMoney(),
        ];
    }

    public function sortByUm(&$profiles)
    {
        usort($profiles, function(Profile $a, Profile $b) {
            return $a->getUmName() <=> $b->getUmName();
        });
    }
}