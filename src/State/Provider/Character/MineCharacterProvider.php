<?php

namespace App\State\Provider\Character;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Character\Character;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class MineCharacterProvider implements ProviderInterface
{


    public function __construct(
        private Security $security,
    ) {
    }

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return Character
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Character
    {
        $character = $this->security->getUser();

        if (!$character instanceof Character) {
            throw new UnauthorizedHttpException('Not authenticated');
        }

        return $character;
    }
}
