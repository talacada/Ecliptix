<?php

namespace App\State\Processor\Character\Inventory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Character\CharacterInventory;
use App\Entity\Item\ElixirDefinition;
use PHPUnit\Framework\Exception;

class CharacterInventoryUseProcessor implements ProcessorInterface
{

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof CharacterInventory);

        if (!$data->getItem()->getDefinition() instanceof ElixirDefinition){
            throw new Exception("Can use activate only elixirs");
        }


    }
}
