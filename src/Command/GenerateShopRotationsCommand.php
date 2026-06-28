<?php

namespace App\Command;

use App\Repository\Character\CharacterRepository;
use App\Service\Shop\RotationGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

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

        $success = 0;
        $failed = 0;

        foreach ($characters as $character) {
            try {
                $this->generator->generate($character);
                $success++;
            } catch (Throwable $e) {
                $failed++;
                $output->writeln(sprintf(
                    '<error>Character %s failed: %s</error>',
                    $character->getId(),
                    $e->getMessage()
                ));
            }
        }

        $output->writeln(sprintf(
            '<info>Done. %d success, %d failed.</info>',
            $success,
            $failed
        ));

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
