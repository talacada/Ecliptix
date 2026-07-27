<?php

declare(strict_types=1);

namespace App\State\Provider\Character;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Character\Character;
use App\Repository\Character\CharacterRepository;
use App\Repository\FriendRelationRepository;
use App\Security\LoggedInCharacter;
use App\Service\Elixir\ElixirCleanUp;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * @implements ProviderInterface<Character>
 */
class PublicCharacterProvider implements ProviderInterface
{
    public function __construct(
        private CharacterRepository $characterRepository,
        private ElixirCleanUp $elixirCleanUp,
        private LoggedInCharacter $loggedInCharacter,
        private FriendRelationRepository $friendRelationRepository,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Character
    {
        if (isset($uriVariables['id']) && is_numeric($uriVariables['id'])) {
            $character = $this->loggedInCharacter->getCharacter();

            $id = (int) $uriVariables['id'];
            $searchedCharacter = $this->characterRepository->getCharacterById($id);

            if (!$searchedCharacter instanceof Character) {
                throw new NotFoundResourceException('Character not found');
            }

            $this->elixirCleanUp->removeExpired($searchedCharacter);

            if ($this->friendRelationRepository->findRelation($character, $searchedCharacter)) {
                $searchedCharacter->setFriends(true);
            }

            return $searchedCharacter;
        }
        throw new \Exception("Missing parameter 'id' in uriVariables");
    }
}
