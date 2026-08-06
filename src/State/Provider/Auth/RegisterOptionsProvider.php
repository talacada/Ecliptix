<?php

namespace App\State\Provider\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Auth\RegisterOptions\AppearanceGroupDto;
use App\ApiResource\Auth\RegisterOptions\AppearanceOptionDto;
use App\ApiResource\Auth\RegisterOptions\RaceDto;
use App\Entity\Appearance\AppearanceTypeEnum;
use App\Repository\AppearanceOptionRepository;
use App\Repository\RaceRepository;

class RegisterOptionsProvider implements ProviderInterface
{

    public function __construct(
        private AppearanceOptionRepository $appearanceOptionRepository,
        private RaceRepository $raceRepository,
    ) {}

    /* @return RaceDto[] */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $response = [];

        foreach ($this->raceRepository->getAllRaces() as $race) {
            $raceDto = new RaceDto();
            $raceDto->setId($race->getId());
            $raceDto->setName($race->getName());

            $appearanceGroup = new AppearanceGroupDto();

            $allOptionsByRace = $this->appearanceOptionRepository->getAllOptionsByRace($race);

            foreach ($allOptionsByRace as $option) {
                $optionDto = AppearanceOptionDto::fromEntity($option);
                switch ($option->getType()) {
                    case AppearanceTypeEnum::hair : $appearanceGroup->addHair($optionDto); break;
                    case AppearanceTypeEnum::ears : $appearanceGroup->addEars($optionDto); break;
                    case AppearanceTypeEnum::eyes : $appearanceGroup->addEyes($optionDto); break;
                    case AppearanceTypeEnum::mouth : $appearanceGroup->addMouth($optionDto); break;
                    case AppearanceTypeEnum::nose : $appearanceGroup->addNose($optionDto); break;
                }
            }
            $raceDto->setAppearance($appearanceGroup);
            $response[] = $raceDto;
        }

        return $response;
    }
}
