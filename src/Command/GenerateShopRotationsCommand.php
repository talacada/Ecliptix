<?php

namespace App\Command;

use App\Repository\Character\CharacterRepository;
use App\Service\Shop\RotationGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:shop:generate-rotations')]
class GenerateShopRotationsCommand extends Command
{
    public function __construct(
        private RotationGenerator $generator,
        private CharacterRepository $characterRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $characters = $this->characterRepository->findAll();

        foreach ($characters as $character) {
            $this->generator->generate($character);
        }

        $output->writeln(sprintf('<info>Done. %d rotations generated.</info>', count($characters)));

        return Command::SUCCESS;
    }
}
