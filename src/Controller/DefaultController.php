<?php

namespace App\Controller;

use App\Form\KillsStats\SearchType;
use App\Model\KillsStats\Search;
use App\Repository\KillsStats\PlaneRepository;
use App\Repository\Profile\ProfileRepository;
use App\Service\Erepublik\KillsStats;
use App\Utils\FileHelper;
use App\Utils\KillStats\CsvToProfiles;
use App\Utils\KillStats\ProfileProvider;
use App\Utils\KillStats\ProfilesToCsv;
use App\Utils\KillStats\StatsToCsv;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
     * @var PlaneRepository
     */
    private $planeRepository;

    /**
     * @var ProfileRepository
     */
    private $profileRepository;

    /**
     * @var FileHelper
     */
    private $fileHelper;

    /**
     * @var StatsToCsv
     */
    private $statsToCsv;

    /**
     * DefaultController constructor.
     * @param KillsStats        $killsStats
     * @param CsvToProfiles     $csvToProfiles
     * @param ProfilesToCsv     $profilesToCsv
     * @param PlaneRepository   $planeRepository
     * @param ProfileRepository $profileRepository
     * @param FileHelper        $fileHelper
     * @param StatsToCsv        $statsToCsv
     */
    public function __construct(
        KillsStats $killsStats,
        CsvToProfiles $csvToProfiles,
        ProfilesToCsv $profilesToCsv,
        PlaneRepository $planeRepository,
        ProfileRepository $profileRepository,
        FileHelper $fileHelper,
        StatsToCsv $statsToCsv
    )
    {
        $this->killsStatsService = $killsStats;
        $this->csvToProfiles     = $csvToProfiles;
        $this->profilesToCsv     = $profilesToCsv;
        $this->planeRepository   = $planeRepository;
        $this->profileRepository = $profileRepository;
        $this->fileHelper        = $fileHelper;
        $this->statsToCsv        = $statsToCsv;
    }

    /**
     * @Route("/", name="HOMEPAGE")
     */
    public function index()
    {
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
            $search->setProfiles(ProfileProvider::getSmaProfiles());
//            $search->setProfiles($file ? $this->csvToProfiles->getProfilesFromCsv($file) : ProfileProvider::getSmaProfiles());

            $stats = $this->killsStatsService->run($search->getCookie(), $search->getSemaine());
            $file  = $this->profilesToCsv->getCsvFromProfiles($stats, $search->getSemaine(), $search->getCookie());
            return $this->file($this->fileHelper->getZip($file), sprintf('export-kill-%s.zip', (new \DateTime("NOW"))->format('d-m-Y')));
        }

        return $this->render('default/kills-stats.html.twig', [
            'form'  => $form->createView(),
            'stats' => isset($stats) ? $stats : null,
        ]);
    }

    /**
     * Création de la route "Downloads stats"
     * @Route("/downloads-stats", name="DOWNLOADS_STATS", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function downloadsStats(Request $request)
    {
        return $this->file($this->statsToCsv->getCsvFromStats());
    }
}
