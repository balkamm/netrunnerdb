<?php

namespace AppBundle\Command;

use AppBundle\Entity\Card;
use AppBundle\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class DumpStdCardsCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
        ->setName('nrdb:dump:std:cards')
        ->setDescription('Dump JSON Data of Cards from a Pack')
        ->addArgument(
                'pack_code',
                InputArgument::REQUIRED,
                "Pack Code"
        )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pack_code = $input->getArgument('pack_code');
        
        $pack = $this->entityManager->getRepository('AppBundle:Pack')->findOneBy(['code' => $pack_code]);
        
        if (!$pack) {
            throw new \Exception("Pack [$pack_code] cannot be found.");
        }

        /** @var CardRepository $repository */
        $repository = $this->entityManager->getRepository('AppBundle:Card');
        
        $qb = $repository->createQueryBuilder('c')->where('c.pack = :pack')->setParameter('pack', $pack)->orderBy('c.code');
        
        $cards = $repository->getResult($qb);
        
        $arr = [];

        /** @var Card $card */
        foreach ($cards as $card) {
            $arr[] = $card->normalize();
        }
        
        $output->write(json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $output->writeln("");
    }
}
