<?php

declare(strict_types=1);

namespace App\State\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Attribute\CurrentUserScope;
use App\Security\LoggedInCharacter;
use Doctrine\ORM\QueryBuilder;

final class CurrentUserScopeExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
    ) {
    }

    // Apply extension to COLLECTION

    /**
     * @throws \ReflectionException
     */
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->applyScope($queryBuilder, $resourceClass);
    }

    // Apply extension to SINGLE ITEM

    /**
     * @throws \ReflectionException
     */
    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->applyScope($queryBuilder, $resourceClass);
    }

    /**
     * @throws \ReflectionException
     */
    private function applyScope(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (!class_exists($resourceClass)) {
            return;
        }

        $reflection = new \ReflectionClass($resourceClass);
        $attributes = $reflection->getAttributes(CurrentUserScope::class);

        if (0 === count($attributes)) {
            return;
        }

        /** @var CurrentUserScope $scope */
        $scope = $attributes[0]->newInstance();

        $character = $this->loggedInCharacter->getCharacter();
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere(sprintf('%s.%s = :current_user', $rootAlias, $scope->field))
            ->setParameter('current_user', $character);
    }
}
