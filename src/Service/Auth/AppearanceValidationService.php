<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Entity\Appearance\AppearanceTypeEnum;
use App\Entity\AppearanceOption;
use App\Entity\Race;
use App\Repository\AppearanceOptionRepository;
use App\Repository\RaceRepository;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

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
            throw new UnprocessableEntityHttpException('Invalid race_id');
        }

        $hair = $this->appearanceOptionRepository->getByIdRaceType($hairId, $race, AppearanceTypeEnum::hair);
        if (null === $hair) {
            throw new UnprocessableEntityHttpException('Invalid hair_id');
        }

        $eyes = $this->appearanceOptionRepository->getByIdRaceType($eyesId, $race, AppearanceTypeEnum::eyes);
        if (null === $eyes) {
            throw new UnprocessableEntityHttpException('Invalid eyes_id');
        }

        $mouth = $this->appearanceOptionRepository->getByIdRaceType($mouthId, $race, AppearanceTypeEnum::mouth);
        if (null === $mouth) {
            throw new UnprocessableEntityHttpException('Invalid mouth_id');
        }

        $nose = $this->appearanceOptionRepository->getByIdRaceType($noseId, $race, AppearanceTypeEnum::nose);
        if (null === $nose) {
            throw new UnprocessableEntityHttpException('Invalid nose_id');
        }

        $ears = $this->appearanceOptionRepository->getByIdRaceType($earsId, $race, AppearanceTypeEnum::ears);
        if (null === $ears) {
            throw new UnprocessableEntityHttpException('Invalid ears_id');
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
