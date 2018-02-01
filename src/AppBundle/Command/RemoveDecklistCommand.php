<?php

namespace AppBundle\Command;

use AppBundle\Entity\Decklist;
use AppBundle\Service\DecklistManager;
use Doctrine\ORM\EntityManagerInterface;
use function GuzzleHttp\Psr7\parse_header;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class RemoveDecklistCommand extends Command
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var DecklistManager $decklistManager */
    private $decklistManager;

    public function __construct(EntityManagerInterface $entityManager, DecklistManager $decklistManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->decklistManager = $decklistManager;
    }

    protected function configure()
    {
        $this
            ->setName('nrdb:delete-decklist')
            ->setDescription('Remove one decklist')
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'Id of the decklist'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $decklist_id = $input->getArgument('id');

        /** @var Decklist $decklist */
        $decklist = $this->entityManager->getRepository('AppBundle:Decklist')->find($decklist_id);
        
        $this->decklistManager->removeConstraints($decklist);
        $this->entityManager->remove($decklist);
        
        $this->entityManager->flush();
        
        $output->writeln("Done.");
    }
}
