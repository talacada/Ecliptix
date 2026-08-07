<?php

namespace App\State\Processor\Character;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Character\Character;
use App\State\Provider\Character\MineCharacterProvider;
use Doctrine\ORM\EntityManagerInterface;

class UpdateCharacterProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Character
    {
        // 1. Získáme Doctrine UnitOfWork a zjistíme původní neupravená data entity
        // 2. Porovnáme původní rasa/vzhled vs. nový stav v $data a spočítáme počet změn
        // 4. Pokud $data->getDiamonds() < cena -> vyhodíme výjimku (např. UnprocessableEntityHttpException nebo custom exception s kód 402)
        // 5. Validujeme, že vybrané AppearanceOption patří ke zvolené rase
        // 6. Odečteme diamanty: $data->setDiamonds($data->getDiamonds() - cena)
        // 7. Flushneme změny do databáze a vrátíme $data
    }
}
