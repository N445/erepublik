<?php

namespace App\Controller;

use App\Form\KillsStats\SearchType;
use App\Model\KillsStats\Search;
use App\Repository\KillsStats\PlaneRepository;
use App\Repository\Profile\ProfileRepository;
use App\Service\Erepublik\KillsStats;
use App\Utils\KillStats\CsvToProfiles;
use App\Utils\KillStats\CsvToXls;
use App\Utils\KillStats\ProfileProvider;
use App\Utils\KillStats\ProfilesToCsv;
use DateTime;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use ZipArchive;

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
     * @var CsvToXls
     */
    private $csvToXls;

    /**
     * DefaultController constructor.
     * @param KillsStats        $killsStats
     * @param CsvToProfiles     $csvToProfiles
     * @param ProfilesToCsv     $profilesToCsv
     * @param PlaneRepository   $planeRepository
     * @param ProfileRepository $profileRepository
     * @param CsvToXls          $csvToXls
     */
    public function __construct(
        KillsStats $killsStats,
        CsvToProfiles $csvToProfiles,
        ProfilesToCsv $profilesToCsv,
        PlaneRepository $planeRepository,
        ProfileRepository $profileRepository,
        CsvToXls $csvToXls
    )
    {
        $this->killsStatsService = $killsStats;
        $this->csvToProfiles     = $csvToProfiles;
        $this->profilesToCsv     = $profilesToCsv;
        $this->planeRepository   = $planeRepository;
        $this->profileRepository = $profileRepository;
        $this->csvToXls          = $csvToXls;
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
            $file = $form->get('file')->getData();
            $search->setProfiles($file ? $this->csvToProfiles->getProfilesFromCsv($file) : ProfileProvider::getSmaProfiles());

            $stats = $this->killsStatsService->run($search->getCookie(), $search->getSemaine(), $search->getProfiles());
            $file  = $this->profilesToCsv->getCsvFromProfiles($stats);
            $file  = new File($file);
            $xls   = $this->csvToXls->getXls($file);

            $zip     = new ZipArchive;
            $zipName = sprintf(ProfilesToCsv::UPLOAD_DIR, 'export-kills.zip');
            $dateNow = (new \DateTime("NOW"))->format('d-m-Y');


            if ($zip->open($zipName, ZipArchive::OVERWRITE) === true) {
                $zip->addFile($file->getPathname(), sprintf('export-kill-%s.csv', $dateNow));
                $zip->addFile($xls, sprintf('export-kill-%s.xls', $dateNow));
                $zip->close();
            }
            return $this->file($zipName, sprintf('export-kill-%s.zip', $dateNow));
        }

        return $this->render('default/kills-stats.html.twig', [
            'form'  => $form->createView(),
            'stats' => isset($stats) ? $stats : null,
        ]);
    }
}
