<?php

namespace App\State\Provider\Character;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Character\Character;
use App\Repository\Character\CharacterRepository;
use App\Service\Elixir\ElixirCleanUp;
use Exception;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class PublicCharacterProvider implements ProviderInterface
{
    public function __construct(
        private CharacterRepository $characterRepository,
        private ElixirCleanUp $elixirCleanUp,
    ) {}

    /**
     * @throws Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Character
    {
        if (isset($uriVariables['id']) && is_numeric((int)$uriVariables['id'])) {
            $id = $uriVariables['id'];
            $character = $this->characterRepository->getCharacterById($id);

            if (!$character instanceof Character) {
                throw new NotFoundResourceException('Character not found');
            }

            $this->elixirCleanUp->removeExpired($character);

            return $character;
        }else {
            throw new Exception("Missing parameter 'id' in uriVariables");
        }
    }
}
