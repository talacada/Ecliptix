<?php

declare(strict_types=1);

namespace App\State\Processor\Character\Friends;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Character\Character;
use App\Entity\Character\FriendRelation;
use App\Repository\Character\CharacterRepository;
use App\Repository\FriendRelationRepository;
use App\Security\LoggedInCharacter;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @implements ProcessorInterface<Character, FriendRelation>
 */
class FriendsAddProcessor implements ProcessorInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private FriendRelationRepository $friendRelationRepository,
        private CharacterRepository $characterRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): FriendRelation
    {
        $character = $this->loggedInCharacter->getCharacter();

        if (!isset($uriVariables['id']) || !is_numeric($uriVariables['id'])) {
            throw new \Exception('Invalid ID provided.');
        }

        $followed = $this->characterRepository->getCharacterById((int) $uriVariables['id']);

        if (!$followed) {
            throw new \Exception('Character not found.');
        }

        if ($followed === $character) {
            throw new \Exception('You cannot add yourself.');
        }

        $searchedRelation = $this->friendRelationRepository->findRelation($character, $followed);
        if ($searchedRelation) {
            return $searchedRelation;
        }

        $newRelation = new FriendRelation();
        $newRelation->setFriend($followed);
        $newRelation->setCharacter($character);

        $this->entityManager->persist($newRelation);
        $this->entityManager->flush();

        return $newRelation;
    }
}
