<?php

declare(strict_types=1);

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\SecurityScheme;
use ApiPlatform\OpenApi\OpenApi;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

/* Custom OpenAPI factory - dekoruje originální api_platform.openapi.factory
    1. Přidá 'bearerAuth' security scheme do OpenAPI specifikace
    2. Nastaví globální security na všechny operace
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

        $components = $openApi->getComponents() ?? new \ArrayObject();
        $securitySchemes = $components->getSecuritySchemes() ?? new \ArrayObject();

        // Add JWT Bearer security scheme
        $securitySchemes['bearerAuth'] = new SecurityScheme(
            type: 'http',
            description: 'JWT token obtained from /api/auth/login endpoint',
            scheme: 'bearer',
            bearerFormat: 'JWT',
        );

        $components = $components->withSecuritySchemes($securitySchemes);
        $openApi = $openApi->withComponents($components);

        // Add security globally - musí být array of arrays
        $security = [
            ['bearerAuth' => []],
        ];
        $openApi = $openApi->withSecurity($security);

        return $openApi;
    }
}
