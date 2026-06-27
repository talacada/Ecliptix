<?php

namespace App\Entity\Item;

use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
class ElixirDefinition extends ItemDefinition
{
    public function __construct()
    {
        parent::__construct();
        $this->setBaseDamage(0);
        $this->setBaseCrit(0);
        $this->setBaseHealth(0);
        $this->setRequiredLevel(1);
        $this->setDesiredSlot(ItemSlotEnum::Elixir);
        $this->setRarity(ItemRarityEnum::Common);
    }
}
