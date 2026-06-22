<?php

namespace App\State\Processor\Character\Inventory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

class CharacterInventoryChangeOrderProcessor implements ProcessorInterface
{

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        //TODO next
        dd($data);
    }
}
