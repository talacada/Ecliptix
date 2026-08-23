<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth;

use App\Entity\Appearance\AppearanceTypeEnum;
use App\Entity\AppearanceOption;
use App\Entity\Race;
use App\Repository\AppearanceOptionRepository;
use App\Repository\RaceRepository;
use App\Service\Auth\AppearanceValidationService;
use ArgumentCountError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AppearanceValidationServiceTest extends TestCase
{
    private RaceRepository&MockObject $raceRepository;
    private AppearanceOptionRepository&MockObject $appearanceOptionRepository;
    private AppearanceValidationService $service;

    protected function setUp(): void
    {
        $this->raceRepository = $this->createMock(RaceRepository::class);
        $this->appearanceOptionRepository = $this->createMock(AppearanceOptionRepository::class);

        $this->service = new AppearanceValidationService($this->raceRepository, $this->appearanceOptionRepository);
    }

    public function testVerifiesAppearanceSuccess(): void
    {
        $race = new Race();
        $hair = new AppearanceOption();
        $eyes = new AppearanceOption();
        $mouth = new AppearanceOption();
        $nose = new AppearanceOption();
        $ears = new AppearanceOption();

        $this->raceRepository
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($race);

        $this->appearanceOptionRepository
            ->expects($this->exactly(5))
            ->method('getByIdRaceType')
            ->willReturnMap([
                // Row format: [$id, $race, $type, $return]
                [10, $race, AppearanceTypeEnum::hair, $hair],
                [20, $race, AppearanceTypeEnum::eyes, $eyes],
                [30, $race, AppearanceTypeEnum::mouth, $mouth],
                [40, $race, AppearanceTypeEnum::nose, $nose],
                [50, $race, AppearanceTypeEnum::ears, $ears],
            ]);

        $result = $this->service->verifiesAppearance(
            raceId: 1,
            hairId: 10,
            eyesId: 20,
            mouthId: 30,
            noseId: 40,
            earsId: 50,
        );

        $this->assertSame([
            'race' => $race,
            'hair' => $hair,
            'eyes' => $eyes,
            'mouth' => $mouth,
            'nose' => $nose,
            'ears' => $ears,
        ], $result);
    }

    public function testVerifiesAppearanceThrowsWhenRaceNotFound(): void
    {
        $this->raceRepository
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn(null);

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Invalid race_id');

        $this->service->verifiesAppearance(1, 1, 2, 3, 4, 5);
    }

    #[DataProvider("AppearanceOptionsDataProvider")]
    public function testVerifiesAppearanceThrowsWhenAppearanceNotFound(
        string $errorMessage,
        AppearanceTypeEnum $failingType
    ): void
    {
        $race = new Race();

        $this->raceRepository
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($race);

        $this->appearanceOptionRepository
            ->method('getByIdRaceType')
            ->willReturnCallback(
                function (int $id, Race $race, AppearanceTypeEnum $type) use ($failingType) {
                    if ($type === $failingType) {
                        return null;
                    }
                    return new AppearanceOption();
                }
            );

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage($errorMessage);

        $this->service->verifiesAppearance(
            raceId: 1,
            hairId: 10,
            eyesId: 20,
            mouthId: 30,
            noseId: 40,
            earsId: 50,
        );
    }

    public static function AppearanceOptionsDataProvider(): iterable
    {
        yield 'Hair' => ['Invalid hair_id', AppearanceTypeEnum::hair];
        yield 'Eyes' => ['Invalid eyes_id', AppearanceTypeEnum::eyes];
        yield 'Mouth' => ['Invalid mouth_id', AppearanceTypeEnum::mouth];
        yield 'Nose' => ['Invalid nose_id', AppearanceTypeEnum::nose];
        yield 'Ears' => ['Invalid ears_id', AppearanceTypeEnum::ears];
    }
}
