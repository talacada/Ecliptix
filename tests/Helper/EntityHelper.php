<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use LogicException;
use ReflectionClass;

final class EntityHelper
{
    /**
     * Setting private entity ID via reflection. Entities do not have setId and its needed in tests to control ids.
     */
    public static function setId(object $entity, int $id): void
    {
        self::setProperty($entity, 'id', $id);
    }

    public static function setProperty(object $entity, string $propertyName, mixed $value): void
    {
        $reflectionClass = new ReflectionClass($entity);

        while ($reflectionClass !== false) {
            if ($reflectionClass->hasProperty($propertyName)) {
                $property = $reflectionClass->getProperty($propertyName);
                $property->setValue($entity, $value);

                return;
            }

            $reflectionClass = $reflectionClass->getParentClass();
        }

        throw new LogicException(sprintf(
            'Property $%s does not exist on class %s or any of its parent classes.',
            $propertyName,
            $entity::class
        ));
    }
}
