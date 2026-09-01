<?php

declare(strict_types=1);

namespace App\Story;

use App\Entity\Appearance\AppearanceTypeEnum;
use App\Entity\AppearanceOption;
use App\Entity\Character\ActiveElixir;
use App\Entity\Character\Character;
use App\Entity\Character\CharacterInventory;
use App\Entity\Item\InventoryContainerEnum;
use App\Entity\Item\Item;
use App\Entity\Item\ItemDefinition;
use App\Entity\Item\ItemSlotEnum;
use App\Entity\Race;
use App\Repository\AppearanceOptionRepository;
use App\Repository\Item\ItemDefinitionRepository;
use App\Repository\Leaderboard\LeaderboardRepository;
use App\Repository\RaceRepository;
use App\Service\Item\ItemStatCalculator;
use App\Service\Shop\RotationGenerator;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Random\RandomException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use function assert;
use function Zenstruck\Foundry\faker;

final class CharacterStory
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private RotationGenerator $rotationGenerator,
        private ItemDefinitionRepository $itemDefinitionRepository,
        private LeaderboardRepository $leaderboardRepository,
        private RaceRepository $raceRepository,
        private AppearanceOptionRepository $appearanceOptionRepository,
    ) {
    }

    private const int BATCH_SIZE = 50;
    private const int TOTAL_CHARACTERS = 200;

    /**
     * @throws OptimisticLockException
     * @throws DateMalformedStringException
     * @throws RandomException
     * @throws ORMException
     */
    public function generate(): void
    {
        $races = $this->raceRepository->getAllRaces();
        if ($races === []) {
            return;
        }

        $raceIds = [];
        $optionsByRace = [];

        foreach ($races as $race) {
            $raceId = $race->getId();
            $raceIds[] = $raceId;
            $optionsByRace[$raceId] = [
                AppearanceTypeEnum::hair->value => [],
                AppearanceTypeEnum::eyes->value => [],
                AppearanceTypeEnum::mouth->value => [],
                AppearanceTypeEnum::nose->value => [],
                AppearanceTypeEnum::ears->value => [],
            ];

            $options = $this->appearanceOptionRepository->getAllOptionsByRace($race);
            foreach ($options as $option) {
                $optionsByRace[$raceId][$option->getType()->value][] = $option->getId();
            }
        }

        // Generate bare characters with random PP (leaderboard padding, no equipment needed)
        for ($i = 0; $i < self::TOTAL_CHARACTERS; ++$i) {
            $character = new Character();
            $character->setEmail(faker()->email());
            $character->setUsername(faker()->userName().$i);
            $character->setPasswordHash(
                $this->passwordHasher->hashPassword($character, faker()->password()),
            );
            $character->setPrestigePoints(random_int(0, 10000));
            $this->setRandomAppearance($character, $raceIds, $optionsByRace);
            $this->entityManager->persist($character);

            if (($i + 1) % self::BATCH_SIZE === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                gc_collect_cycles();
            }
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
        gc_collect_cycles();

        // Get PP that puts you around rank 100 (middle of the pack)
        $midRankPp = $this->leaderboardRepository->getPrestigePointsAtRank(100);

        // Default character — fully equipped, at middle rank
        $defaultChar = $this->createCharacter(
            email: 'default@gmail.com',
            password: 'Hesloheslo1',
            username: 'default',
            raceIds: $raceIds,
            optionsByRace: $optionsByRace,
        );
        $defaultChar->setPrestigePoints($midRankPp);
        $this->equipCharacter($defaultChar);
        $this->entityManager->clear();

        $defaultChar = $this->entityManager->find(Character::class, $defaultChar->getId());
        assert($defaultChar instanceof Character);
        $this->rotationGenerator->generate($defaultChar);
    }

    /**
     * @param int[] $raceIds
     * @param array<int, array<string, int[]>> $optionsByRace
     * @throws ORMException
     */
    private function createCharacter(string $email, string $password, string $username, array $raceIds, array $optionsByRace): Character
    {
        $character = new Character();
        $character->setEmail($email);
        $character->setUsername($username);
        $character->setPasswordHash(
            $this->passwordHasher->hashPassword($character, $password),
        );
        $character->setEmailVerified(true);
        $character->setGold(1000000);
        $character->setDiamonds(1000000);
        $this->setRandomAppearance($character, $raceIds, $optionsByRace);
        $this->entityManager->persist($character);

        return $character;
    }

    /**
     * @param int[] $raceIds
     * @param array<int, array<string, int[]>> $optionsByRace
     * @throws ORMException
     */
    private function setRandomAppearance(Character $character, array $raceIds, array $optionsByRace): Character
    {
        $raceId = $raceIds[array_rand($raceIds)];
        $raceOptions = $optionsByRace[$raceId];

        $hairId = $raceOptions[AppearanceTypeEnum::hair->value][array_rand($raceOptions[AppearanceTypeEnum::hair->value])];
        $eyesId = $raceOptions[AppearanceTypeEnum::eyes->value][array_rand($raceOptions[AppearanceTypeEnum::eyes->value])];
        $mouthId = $raceOptions[AppearanceTypeEnum::mouth->value][array_rand($raceOptions[AppearanceTypeEnum::mouth->value])];
        $noseId = $raceOptions[AppearanceTypeEnum::nose->value][array_rand($raceOptions[AppearanceTypeEnum::nose->value])];
        $earsId = $raceOptions[AppearanceTypeEnum::ears->value][array_rand($raceOptions[AppearanceTypeEnum::ears->value])];

        $race = $this->entityManager->getReference(Race::class, $raceId);
        $hair = $this->entityManager->getReference(AppearanceOption::class, $hairId);
        $eyes = $this->entityManager->getReference(AppearanceOption::class, $eyesId);
        $mouth = $this->entityManager->getReference(AppearanceOption::class, $mouthId);
        $nose = $this->entityManager->getReference(AppearanceOption::class, $noseId);
        $ears = $this->entityManager->getReference(AppearanceOption::class, $earsId);

        assert($race instanceof Race);
        assert($hair instanceof AppearanceOption);
        assert($eyes instanceof AppearanceOption);
        assert($mouth instanceof AppearanceOption);
        assert($nose instanceof AppearanceOption);
        assert($ears instanceof AppearanceOption);

        $character->setRace($race);
        $character->setHair($hair);
        $character->setEyes($eyes);
        $character->setMouth($mouth);
        $character->setNose($nose);
        $character->setEars($ears);

        return $character;
    }

    /**
     * @throws DateMalformedStringException
     */
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
                new DateTimeImmutable()->modify('+'.$elixirDef->getDurationSeconds().' seconds'),
            );
            $this->entityManager->persist($active);
        }

        $this->entityManager->flush();
    }

    private function createItemFromDefinition(ItemDefinition $def): Item
    {
        $item = new Item();
        $item->setDefinition($def);
        [$bd, $bc, $bh] = ItemStatCalculator::rollBonusStats($def);
        $item->setBonusDamage($bd);
        $item->setBonusCrit($bc);
        $item->setBonusHealth($bh);
        $this->entityManager->persist($item);

        return $item;
    }
}

