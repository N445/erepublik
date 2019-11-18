<?php

namespace App\Utils\KillStats;

use App\Entity\KillsStats\Plane;
use App\Entity\Profile\Profile;
use App\Utils\MondayHelper;

class ProfilesToCsv
{
    const PROFILE_URL = "https://www.erepublik.com/fr/economy/donate-money/%s";
    const UPLOAD_DIR  = __DIR__ . '/../../../var/tmp/%s';
    const UPLOAD_CSV  = 'export-tmp.csv';
    const UPLOAD_XLS  = 'export-tmp.xls';

    private $killsTotal  = 0;

    private $monneyTotal = 0;

    /**
     * @var \DateTime
     */
    private $semaine;


    /**
     * @param $profiles
     * @param $semaine
     * @return string
     * @throws \Exception
     */
    public function getCsvFromProfiles($profiles, $semaine)
    {
        $this->semaine = MondayHelper::getSemaineDateTime($semaine);
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
        $lastStat = $this->getCurrentStat($profile);
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

    /**
     * @param Profile $profile
     * @return Plane|mixed
     */
    private function getCurrentStat(Profile $profile)
    {
        foreach ($profile->getPlanes() as $plane) {
            if ($plane->getDate()->format('d/m/Y') === $this->semaine->format('d/m/Y')) {
                return $plane;
            }
        }
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