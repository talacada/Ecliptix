<?php


use App\Entity\Appearance\AppearanceTypeEnum;
use App\Repository\AppearanceOptionRepository;
use App\Repository\RaceRepository;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AppearanceValidationService
{

    public function __construct(
        private RaceRepository $raceRepository,
        private AppearanceOptionRepository $appearanceOptionRepository,
    ) {}

    /**
     * @return array <string, AppearanceOption>
     */
    public function verifiesAppearance(
        int $raceId,
        int $hairId,
        int $eyesId,
        int $mouthId,
        int $noseId,
        int $earsId
    ): array
    {

        $race = $this->raceRepository->getById($raceId);
        if ($race === null) {
            throw new UnprocessableEntityHttpException('Invalid race_id');
        }

        $options = [
            'race' => $race,
            'hair' => $this->appearanceOptionRepository->getByIdRaceType($hairId, $race, AppearanceTypeEnum::hair),
            'eyes' => $this->appearanceOptionRepository->getByIdRaceType($eyesId, $race, AppearanceTypeEnum::eyes),
            'mouth' => $this->appearanceOptionRepository->getByIdRaceType($mouthId, $race, AppearanceTypeEnum::mouth),
            'nose' => $this->appearanceOptionRepository->getByIdRaceType($noseId, $race, AppearanceTypeEnum::nose),
            'ears' => $this->appearanceOptionRepository->getByIdRaceType($earsId, $race, AppearanceTypeEnum::ears),
        ];

        if ($options['hair'] === null) {
            throw new UnprocessableEntityHttpException('Invalid hair_id');
        }
        if ($options['eyes'] === null){
            throw new UnprocessableEntityHttpException('Invalid eyes_id');
        }
        if ($options['mouth'] === null){
            throw new UnprocessableEntityHttpException('Invalid mouth_id');
        }
        if ($options['nose'] === null){
            throw new UnprocessableEntityHttpException('Invalid nose_id');
        }
        if ($options['ears'] === null){
            throw new UnprocessableEntityHttpException('Invalid ears_id');
        }

        return $options;
    }

}
