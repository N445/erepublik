<?php

namespace App\Command;

use App\Repository\Profile\ProfileRepository;
use App\Service\Erepublik\ProfilePopulator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProfileUpdateDataCommand extends Command
{
    protected static $defaultName = 'app:profile:update-data';

    /**
     * @var ProfileRepository
     */
    private $profileRepository;

    /**
     * @var ProfilePopulator
     */
    private $profilePopulator;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        string $name = null,
        ProfileRepository $profileRepository,
        ProfilePopulator $profilePopulator,
        EntityManagerInterface $em
    )
    {
        $this->profileRepository = $profileRepository;
        $this->profilePopulator  = $profilePopulator;
        $this->em                = $em;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Update les data des profiles');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $i = 0;
        foreach ($this->profileRepository->findAll() as $profile) {
            $this->em->persist($this->profilePopulator->setProfileInformations($profile));
            $i++;
        }

        $this->em->flush();

        $io->success(sprintf('%s profiles mis a jour', $i));

        return 0;
    }
}
