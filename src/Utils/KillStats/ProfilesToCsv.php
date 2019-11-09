<?php

namespace App\Utils\KillStats;

use App\Entity\KillsStats\Plane;
use App\Entity\Profile\Profile;

class ProfilesToCsv
{
    /**
     * @param Profile[] $profiles
     * @return string
     */
    public function getCsvFromProfiles($profiles)
    {
        $this->sort($profiles);
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
        /** @var Plane $lastStat */
        $lastStat = $profile->getPlanes()->last();
        return [
            $profile->getIdentifier(),
            $profile->getName(),
            $profile->getUnitemilitaire()->getName(),
            $lastStat ? $lastStat->getKills() : null,
            $lastStat ? $lastStat->getMoney() : null,
        ];
    }

    private function sort(&$profiles)
    {

        usort($profiles, function (Profile $a, Profile $b) {
            if ($a->getPlanes()->count() == 0 || $b->getPlanes()->count() == 0) {
                return 0;
            }
            return $a->getPlanes()->last()->getKills() <=> $b->getPlanes()->last()->getKills();
        });
        usort($profiles, function (Profile $a, Profile $b) {
            return $a->getUnitemilitaire()->getName() <=> $b->getUnitemilitaire()->getName();
        });
    }
}