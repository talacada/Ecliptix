<?php

namespace App\State\Provider\Character;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Item\ItemViewDTO;
use App\Security\LoggedInCharacter;

class CharacterInventoryProvider implements ProviderInterface
{

    public function __construct(
        private loggedInCharacter $loggedInCharacter
    ){ }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof GetCollection) {
            $character = $this->loggedInCharacter->getCharacter();
            $inventories = $character->getCharacterInventories();

            foreach ($inventories as $inv) {
                $dto = new ItemViewDTO();
                $dto->buildDtoFromItem(
                    $inv->getItem()
                );
                $inv->setItemViewDTO($dto);
            }

            return $inventories->toArray();
        }
    }
}
