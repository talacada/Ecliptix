<?php

declare(strict_types=1);

namespace App\Support\Infrastructure\Api;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\SecurityScheme;
use ApiPlatform\OpenApi\OpenApi;
use ArrayObject;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

/*
 * Decorates the original api_platform.openapi.factory.
 * Adds the bearerAuth security scheme to the OpenAPI specification and applies it to all operations.
 */
#[AsDecorator(decorates: 'api_platform.openapi.factory')]
readonly class JwtOpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        $components = $openApi->getComponents();
        $securitySchemes = $components->getSecuritySchemes() ?? new ArrayObject();

        // Add JWT Bearer security scheme
        $securitySchemes['bearerAuth'] = new SecurityScheme(
            type: 'http',
            description: 'JWT token obtained from /api/auth/login endpoint',
            scheme: 'bearer',
            bearerFormat: 'JWT',
        );

        $components = $components->withSecuritySchemes($securitySchemes);
        $openApi = $openApi->withComponents($components);

        // Global security requirements must be an array of arrays.
        $security = [
            ['bearerAuth' => []],
        ];
        $openApi = $openApi->withSecurity($security);

        return $openApi;
    }
}
