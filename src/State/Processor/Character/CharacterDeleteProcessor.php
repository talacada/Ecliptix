<?php

namespace App\State\Processor\Character;

use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Character\Character;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class CharacterDeleteProcessor implements ProcessorInterface
{

	public function __construct(
        private Security $security,
        private RemoveProcessor $removeProcessor,
    ) {
	}


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $character = $this->security->getUser();

        if (!$character instanceof Character) {
            throw new UnauthorizedHttpException('Not authenticated');
        }

        $this->removeProcessor->process($character, $operation, $uriVariables, $context);
    }
}
