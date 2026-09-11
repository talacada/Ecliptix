<?php

declare(strict_types=1);

namespace App\Tests\Integration\Shop;

use App\Tests\Integration\AbstractApiTestCase;

class ShopRotationApiTest extends AbstractApiTestCase
{

    // TODO - testGetShopRotationGeneratesDailyRotationIfMissing - Overit, ze GET /api/shop/rotation automaticky vygeneruje denni nabidku, pokud postava zadnou nema
    // TODO - testGetShopRotationReturnsCurrentActiveOffers - Overit, ze GET /api/shop/rotation vrati aktivni nabidky rotace s cenami a atributy predmetu
    // TODO - testShopRotationRequiresAuthentication - Overit 401 Unauthorized bez platneho JWT tokenu
}
