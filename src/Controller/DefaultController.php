<?php

namespace App\Controller;

use App\Form\KillsStats\SearchType;
use App\Model\KillsStats\Search;
use App\Service\Erepublik\KillsStats;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{

    /**
     * @var KillsStats
     */
    private $killsStatsService;

    /**
     * DefaultController constructor.
     * @param KillsStats $killsStats
     */
    public function __construct(KillsStats $killsStats)
    {
        $this->killsStatsService = $killsStats;
    }

    /**
     * @Route("/", name="HOMEPAGE")
     */
    public function index()
    {
//        $this->killsStats->setProfilesAndUmIds([
//            '9541670',
//            '8612563',
//        ])->setCookie('fgc2eo5opmid7ecdp105pq9521')
//        ;
//        dump($this->killsStats->run());
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    /**
     * @Route("/kills-stats", name="KILLS_STATS")
     */
    public function killsStats(Request $request)
    {
        $search = new Search();
        $form   = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->killsStatsService->setProfilesAndUmIds($search->getProfiles())
                                    ->setCookie($search->getCookie())
            ;
            $stats = $this->killsStatsService->run();
        }

        return $this->render('default/kills-stats.html.twig', [
            'form'  => $form->createView(),
            'stats' => isset($stats) ? $stats : null,
        ]);
    }
}
