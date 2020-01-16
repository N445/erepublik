<?php

namespace App\Utils\KillStats;

use App\Entity\KillsStats\Plane;
use App\Entity\Profile\Profile;
use App\Repository\KillsStats\PlaneRepository;

class StatsToCsv
{
    const UPLOAD_DIR = __DIR__ . '/../../../var/tmp/%s';
    const UPLOAD_CSV = 'export-kills-%s.csv';

    /**
     * @var PlaneRepository
     */
    private $planeRepository;

    /**
     * StatsToCsv constructor.
     * @param PlaneRepository $planeRepository
     */
    public function __construct(PlaneRepository $planeRepository)
    {
        $this->planeRepository = $planeRepository;
    }

    public function getCsvFromStats()
    {
        $path = sprintf(self::UPLOAD_DIR, sprintf(self::UPLOAD_CSV, (new \DateTime())->format('d-m-Y')));

        $fp = fopen($path, 'w');

        fputcsv($fp, $this->getHeaders());

        /** @var Plane $planeStat */
        foreach ($this->planeRepository->getPlanesStats() as $planeStat) {
            fputcsv($fp, $this->getStatsArray($planeStat));
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
            'Identifiant', 'Nom', 'Unité militaire', 'Nombre de kills', 'Argent', 'date', 'semaine',
        ];
    }

    /**
     * @param Plane $planeStat
     * @return array
     */
    private function getStatsArray(Plane $planeStat)
    {
        return [
            $planeStat->getProfile()->getIdentifier(),
            $planeStat->getProfile()->getName(),
            $planeStat->getProfile()->getUnitemilitaire()->getName(),
            $planeStat->getKills(),
            $planeStat->getMoney(),
            $planeStat->getDate()->format('d-m-Y'),
            $planeStat->getDate()->format('W'),
        ];
    }
}