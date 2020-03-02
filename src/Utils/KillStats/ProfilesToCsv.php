<?php

namespace App\Utils\KillStats;

use App\Clients\Erepublik;
use App\Entity\KillsStats\Plane;
use App\Entity\Profile\Profile;
use App\Utils\MondayHelper;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Symfony\Component\DomCrawler\Crawler;

class ProfilesToCsv
{
    const PROFILE_URL = "https://www.erepublik.com/en/economy/donate-money/%s";
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
     * @var string
     */
    private $cookie;

    /**
     * @var Erepublik
     */
    private $erepublikClient;

    /**
     * @var float
     */
    private $smaMonneytotal;

    public function __construct(Erepublik $erepublikClient)
    {
        $this->erepublikClient = $erepublikClient;
    }


    /**
     * @param $profiles
     * @param $semaine
     * @return string
     * @throws \Exception
     */
    public function getCsvFromProfiles($profiles, $semaine, $cookie)
    {
        $this->semaine = MondayHelper::getSemaineDateTime($semaine);
        $this->cookie  = $cookie;
        $this->setSMAMonney();
        $this->sort($profiles);
        $path = sprintf(self::UPLOAD_DIR, self::UPLOAD_CSV);

        $fp = fopen($path, 'w');

        fputcsv($fp, $this->getHeaders());

        /** @var Profile $profile */
        foreach ($profiles as $profile) {
            if ($profile->getIsAlive() && $profile->getIsActive()) {
                fputcsv($fp, $this->getProfileArray($profile));
            }
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
        $lastStat          = $this->getCurrentStat($profile);
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

    /**
     * @param $fp
     */
    private function getFooter(&$fp)
    {
        fputcsv($fp, [null]);
        fputcsv($fp, [
            null,
        ]);
        fputcsv($fp, [
            null, null, null,
            'Total argent SMA',
            number_format($this->smaMonneytotal, 2, ',', ' '),
        ]);
        fputcsv($fp, [
            null, null, null,
            'Total Kills',
            number_format($this->killsTotal, 0, ',', ' '),
        ]);
        fputcsv($fp, [
            null, null, null,
            'Total argent donné',
            number_format($this->monneyTotal, 0, ',', ' '),
        ]);
        fputcsv($fp, [
            null, null, null,
            'Argent restant',
            number_format($this->smaMonneytotal - $this->monneyTotal, 2, ',', ' '),
        ]);
//        fputcsv($fp, [
//            null, null,
//            'Total argent SMA',
//            'Total Kills',
//            'Total argent donné',
//            'Argent restant',
//        ]);

//        fputcsv($fp, [
//            null, null,
//            number_format($this->smaMonneytotal, 2, ',', ' '),
//            number_format($this->killsTotal, 0, ',', ' '),
//            number_format($this->monneyTotal, 0, ',', ' '),
//            number_format($this->smaMonneytotal - $this->monneyTotal, 2, ',', ' '),
//        ]);
    }

    /**
     * @param Profile[] $profiles
     */
    private function sort(&$profiles)
    {
        $this->sortUn($profiles);
        $this->sortDeux($profiles);
    }

    /**
     * @param Profile[] $profiles
     */
    private function sortUn(&$profiles)
    {
        usort($profiles, function (Profile $a, Profile $b) {
            return $a->getName() <=> $b->getName();
        });
    }

    /**
     * @param Profile[] $profiles
     */
    private function sortDeux(&$profiles)
    {
        usort($profiles, function (Profile $a, Profile $b) {
            return $a->getUnitemilitaire()->getName() <=> $b->getUnitemilitaire()->getName();
        });
    }

    private function setSMAMonney()
    {
        $setCookie = new SetCookie();
        $setCookie->setPath('/');
        $setCookie->setDomain('.erepublik.com');
        $setCookie->setName('erpk');
        $setCookie->setValue($this->cookie);
        $this->cookie = new CookieJar();
        $this->cookie->setCookie($setCookie);

        $response = $this->erepublikClient->get('/fr/economy/citizen-accounts/3057326', [
            'cookies' => $this->cookie,
        ])->getBody()->getContents()
        ;
        $crawler  = new Crawler($response);
        if (!!$crawler->filter('.push_right')) {
            $this->smaMonneytotal = -1;
            return;
        }
        $this->smaMonneytotal = floatval(trim($crawler->filter('.push_right')->text()));

    }
}