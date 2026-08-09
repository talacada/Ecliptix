<?php

namespace App\State\Processor\Character;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Character\Character;
use App\Enum\CurrencyEnum;
use App\Enum\GameCostEnum;
use App\Service\Auth\AppearanceValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * @implements ProcessorInterface<Character, Character>
 */
class UpdateCharacterProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AppearanceValidationService $appearanceValidationService,
    ) {}

    /**
     * @throws Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Character
    {
        /* @var Character $prevEntity */
        $prevEntity = $this->entityManager->getUnitOfWork()->getOriginalEntityData($data);
        $character = $data;

        if (
            $prevEntity['race'] === $character->getRace() &&
            $prevEntity['hair'] === $character->getHair() &&
            $prevEntity['eyes'] === $character->getEyes() &&
            $prevEntity['mouth'] === $character->getMouth() &&
            $prevEntity['nose'] === $character->getNose() &&
            $prevEntity['ears'] === $character->getEars() &&
            $prevEntity['username'] === $character->getUsername()
        ) {
            throw new Exception('Nothing changed');
        }

        $this->appearanceValidationService->verifiesAppearance(
            $character->getRace()->getId(),
            $character->getHair()->getId(),
            $character->getEyes()->getId(),
            $character->getMouth()->getId(),
            $character->getNose()->getId(),
            $character->getEars()->getId(),
        );

        $cost = GameCostEnum::CHANGE_APPEARANCE;
        match ($cost->getCurrency()) {
            CurrencyEnum::DIAMONDS => $character->subtractDiamonds($cost->getAmount()),
            CurrencyEnum::GOLD => $character->subtractGold($cost->getAmount()),
        };

        $this->entityManager->flush();
        return $character;
    }
}
