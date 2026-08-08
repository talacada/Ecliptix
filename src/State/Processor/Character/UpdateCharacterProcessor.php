<?php

namespace App\State\Processor\Character;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Character\Character;
use App\Enum\CurrencyEnum;
use App\Enum\GameCostEnum;
use AppearanceValidationService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @implements ProcessorInterface<Character, Character>
 */
class UpdateCharacterProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AppearanceValidationService $appearanceValidationService,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Character
    {
        /* @var Character $prevEntity */
        $prevEntity = $this->entityManager->getUnitOfWork()->getOriginalEntityData($data);
        $character = $data;

        $newAppearanceOptions = $this->appearanceValidationService->verifiesAppearance(
            $character->getRace()->getId(),
            $character->getHair()->getId(),
            $character->getEyeColor()->getId(),
            $character->getMouth()->getId(),
            $character->getNose()->getId(),
            $character->getEat()->getId(),
        );

        $cost = GameCostEnum::CHANGE_APPEARANCE;
        match ($cost->getCurrency()) {
            CurrencyEnum::DIAMONDS => $character->subtractDiamonds($cost->getAmount()),
            CurrencyEnum::GOLD => $character->subtractGold($cost->getAmount()),
        };

        //TODO here continue - set only changed values

        // 2. Porovnáme původní rasa/vzhled vs. nový stav v $data a spočítáme počet změn
        // 4. Pokud $data->getDiamonds() < cena -> vyhodíme výjimku (např. UnprocessableEntityHttpException nebo custom exception s kód 402)
        // 5. Validujeme, že vybrané AppearanceOption patří ke zvolené rase
        // 6. Odečteme diamanty: $data->setDiamonds($data->getDiamonds() - cena)
        // 7. Flushneme změny do databáze a vrátíme $data
    }
}
