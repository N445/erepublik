<?php

namespace App\Command;

use App\Service\Erepublik\KillsStats;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestCommand extends Command
{
    protected static $defaultName = 'app:test';

    /**
     * @var KillsStats
     */
    private $killsStats;

    /**
     * TestCommand constructor.
     * @param string|null $name
     * @param KillsStats  $killsStats
     */
    public function __construct(string $name = null, KillsStats $killsStats)
    {
        $this->killsStats = $killsStats;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->killsStats
            ->setCookie('lrcmaqh5oua9647okpv6876ba1')
            ->setProfilesAndUmIds([
                '9541670',
                '9543015',
                '9541668',
                '8612563',
            ])
            ->setSemaine(1)
        ;
        dump($this->killsStats->run());
        $io->success('Fin.');

        return 0;
    }
}
