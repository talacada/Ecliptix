<?php

declare(strict_types=1);

namespace App\Story;

use App\Entity\ActiveElixir;
use App\Entity\Character\Character;
use App\Entity\Character\CharacterInventory;
use App\Entity\Item\InventoryContainerEnum;
use App\Entity\Item\Item;
use App\Entity\Item\ItemDefinition;
use App\Entity\Item\ItemSlotEnum;
use App\Repository\Item\ItemDefinitionRepository;
use App\Service\Item\ItemFactory;
use App\Service\Shop\RotationGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use function Zenstruck\Foundry\faker;

final class CharacterStory
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private RotationGenerator $rotationGenerator,
        private ItemDefinitionRepository $itemDefinitionRepository,
        private ItemFactory $itemFactory,
    ) {
    }

    public function generate(): void
    {
        $characters = [];

        $defaultChar = $this->createCharacter(
            email: 'default@gmail.com',
            password: 'Hesloheslo1',
            username: 'default',
        );
        $this->equipCharacter($defaultChar);
        $characters[] = $defaultChar;

        for ($i = 0; $i < 2; ++$i) {
            $opponent = $this->createCharacter(
                email: faker()->email(),
                password: faker()->password(),
                username: faker()->userName(),
            );
            $this->equipCharacter($opponent);
            $characters[] = $opponent;
        }

        foreach ($characters as $character) {
            $this->rotationGenerator->generate($character);
        }
    }

    private function createCharacter(string $email, string $password, string $username): Character
    {
        $character = new Character();
        $character->setEmail($email);
        $character->setUsername($username);
        $character->setPasswordHash(
            $this->passwordHasher->hashPassword($character, $password),
        );
        $this->entityManager->persist($character);

        return $character;
    }

    private function equipCharacter(Character $character): void
    {
        $equippedSlots = [
            ItemSlotEnum::Weapon,
            ItemSlotEnum::Armour,
            ItemSlotEnum::RingLeft,
        ];
        foreach ($equippedSlots as $slot) {
            $def = $this->itemDefinitionRepository->findRandomBySlot($slot);
            if (null === $def) {
                continue;
            }
            $item = $this->createItemFromDefinition($def);
            $inv = new CharacterInventory();
            $inv->setCharacter($character);
            $inv->setItem($item);
            $inv->setContainer(InventoryContainerEnum::Equipped);
            $inv->setPosition(0);
            $this->entityManager->persist($inv);
        }

        $backpackItems = [
            [ItemSlotEnum::Helmet, 1],
            [ItemSlotEnum::Necklace, 2],
        ];
        foreach ($backpackItems as [$slot, $position]) {
            $def = $this->itemDefinitionRepository->findRandomBySlot($slot);
            if (null === $def) {
                continue;
            }
            $item = $this->createItemFromDefinition($def);
            $inv = new CharacterInventory();
            $inv->setCharacter($character);
            $inv->setItem($item);
            $inv->setContainer(InventoryContainerEnum::Backpack);
            $inv->setPosition($position);
            $this->entityManager->persist($inv);
        }

        $elixirDef = $this->itemDefinitionRepository->findRandomElixir();
        if (null !== $elixirDef) {
            $elixirItem = new Item();
            $elixirItem->setDefinition($elixirDef);
            $elixirItem->setBonusDamage(0);
            $elixirItem->setBonusCrit(0);
            $elixirItem->setBonusHealth(0);
            $this->entityManager->persist($elixirItem);

            $inv = new CharacterInventory();
            $inv->setCharacter($character);
            $inv->setItem($elixirItem);
            $inv->setContainer(InventoryContainerEnum::Backpack);
            $inv->setPosition(3);
            $this->entityManager->persist($inv);

            $active = new ActiveElixir();
            $active->setCharacter($character);
            $active->setItemDefinition($elixirDef);
            $active->setExpiresAt(
                (new \DateTimeImmutable())->modify('+'.$elixirDef->getDurationSeconds().' seconds'),
            );
            $this->entityManager->persist($active);
        }

        $this->entityManager->flush();
    }

    private function createItemFromDefinition(ItemDefinition $def): Item
    {
        $item = new Item();
        $item->setDefinition($def);
        [$bd, $bc, $bh] = $this->itemFactory->rollBonusStats($def);
        $item->setBonusDamage($bd);
        $item->setBonusCrit($bc);
        $item->setBonusHealth($bh);
        $this->entityManager->persist($item);

        return $item;
    }
}
