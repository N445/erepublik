<?php

namespace App\Controller;

use App\Form\KillsStats\SearchType;
use App\Model\KillsStats\Search;
use App\Service\Erepublik\KillsStats;
use App\Utils\KillStats\CsvToProfiles;
use App\Utils\KillStats\ProfileProvider;
use App\Utils\KillStats\ProfilesToCsv;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{

    /**
     * @var KillsStats
     */
    private $killsStatsService;

    /**
     * @var CsvToProfiles
     */
    private $csvToProfiles;

    /**
     * @var ProfilesToCsv
     */
    private $profilesToCsv;

    /**
     * DefaultController constructor.
     * @param KillsStats    $killsStats
     * @param CsvToProfiles $csvToProfiles
     * @param ProfilesToCsv $profilesToCsv
     */
    public function __construct(KillsStats $killsStats, CsvToProfiles $csvToProfiles, ProfilesToCsv $profilesToCsv)
    {
        $this->killsStatsService = $killsStats;
        $this->csvToProfiles     = $csvToProfiles;
        $this->profilesToCsv     = $profilesToCsv;
    }

    /**
     * @Route("/", name="HOMEPAGE")
     */
    public function index()
    {
        dump(ProfileProvider::getSmaProfiles());
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    /**
     * @Route("/kills-stats", name="KILLS_STATS")
     * @param Request $request
     * @return BinaryFileResponse|Response
     * @throws \Exception
     */
    public function killsStats(Request $request)
    {
        $search = new Search();
        $form   = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $search->setProfiles($file ? $this->csvToProfiles->getProfilesFromCsv($file) : ProfileProvider::getSmaProfiles());
            $this->killsStatsService->setProfilesAndUmIds($search->getProfiles())
                                    ->setCookie($search->getCookie())
                                    ->setSemaine($search->getSemaine())
            ;
            $stats = $this->killsStatsService->run();
            $file  = $this->profilesToCsv->getCsvFromProfiles($stats);
            $file  = new File($file);
            return $this->file($file, sprintf('export-kill-%s.csv', (new \DateTime("NOW"))->format('d-m-Y')));
        }

        return $this->render('default/kills-stats.html.twig', [
            'form'  => $form->createView(),
            'stats' => isset($stats) ? $stats : null,
        ]);
    }
}
