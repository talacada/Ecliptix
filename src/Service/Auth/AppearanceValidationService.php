<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Entity\Appearance\AppearanceTypeEnum;
use App\Entity\AppearanceOption;
use App\Entity\Race;
use App\Exception\Appearance\InvalidAppearanceException;
use App\Repository\AppearanceOptionRepository;
use App\Repository\RaceRepository;

class AppearanceValidationService
{
    public function __construct(
        private RaceRepository $raceRepository,
        private AppearanceOptionRepository $appearanceOptionRepository,
    ) {
    }

    /**
     * @return array{
     *     race: Race,
     *     hair: AppearanceOption,
     *     eyes: AppearanceOption,
     *     mouth: AppearanceOption,
     *     nose: AppearanceOption,
     *     ears: AppearanceOption
     * }
     */
    public function verifiesAppearance(
        int $raceId,
        int $hairId,
        int $eyesId,
        int $mouthId,
        int $noseId,
        int $earsId,
    ): array {
        $race = $this->raceRepository->getById($raceId);
        if (null === $race) {
            throw new InvalidAppearanceException('race_id');
        }

        $hair = $this->appearanceOptionRepository->getByIdRaceType($hairId, $race, AppearanceTypeEnum::hair);
        if (null === $hair) {
            throw new InvalidAppearanceException('hair_id');
        }

        $eyes = $this->appearanceOptionRepository->getByIdRaceType($eyesId, $race, AppearanceTypeEnum::eyes);
        if (null === $eyes) {
            throw new InvalidAppearanceException('eyes_id');
        }

        $mouth = $this->appearanceOptionRepository->getByIdRaceType($mouthId, $race, AppearanceTypeEnum::mouth);
        if (null === $mouth) {
            throw new InvalidAppearanceException('mouth_id');
        }

        $nose = $this->appearanceOptionRepository->getByIdRaceType($noseId, $race, AppearanceTypeEnum::nose);
        if (null === $nose) {
            throw new InvalidAppearanceException('nose_id');
        }

        $ears = $this->appearanceOptionRepository->getByIdRaceType($earsId, $race, AppearanceTypeEnum::ears);
        if (null === $ears) {
            throw new InvalidAppearanceException('ears_id');
        }

        return [
            'race' => $race,
            'hair' => $hair,
            'eyes' => $eyes,
            'mouth' => $mouth,
            'nose' => $nose,
            'ears' => $ears,
        ];
    }
}
