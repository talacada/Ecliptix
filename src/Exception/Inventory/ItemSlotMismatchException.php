<?php

declare(strict_types=1);

namespace App\Exception\Inventory;

use App\Exception\DomainException;
use App\Item\Definition\ItemSlotEnum;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(Response::HTTP_BAD_REQUEST)]
class ItemSlotMismatchException extends DomainException
{
    public function __construct(
        private readonly ?ItemSlotEnum $equippedSlot = null,
        private readonly ?ItemSlotEnum $targetSlot = null,
        string $message = '',
    ) {
        if ('' === $message) {
            $message = (null !== $this->equippedSlot && null !== $this->targetSlot)
                ? sprintf('Items do not match slots. Equipped item type "%s" cannot swap with "%s".', $this->equippedSlot->value, $this->targetSlot->value)
                : 'Items do not match slots.';
        }

        parent::__construct($message);
    }

    public function getEquippedSlot(): ?ItemSlotEnum
    {
        return $this->equippedSlot;
    }

    public function getTargetSlot(): ?ItemSlotEnum
    {
        return $this->targetSlot;
    }
}
