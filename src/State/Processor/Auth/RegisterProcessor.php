<?php

declare(strict_types=1);

namespace App\State\Processor\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Auth\RegisterInput;
use App\Entity\Appearance\AppearanceTypeEnum;
use App\Entity\Character\Character;
use App\Repository\AppearanceOptionRepository;
use App\Repository\Character\CharacterRepository;
use App\Repository\RaceRepository;
use App\Service\Auth\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @implements ProcessorInterface<RegisterInput, Response>
 */
readonly class RegisterProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private CharacterRepository $characterRepository,
        private RaceRepository $raceRepository,
        private AppearanceOptionRepository $appearanceOptionRepository,
        private EmailVerificationService  $emailVerificationService,
        private MessageBusInterface $bus
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): Response {
        if (null !== $this->characterRepository->findOneBy(['email' => $data->getEmail()])) {
            throw new UnprocessableEntityHttpException('Email already registered');
        }

        if (null !== $this->characterRepository->findOneBy(['username' => $data->getUsername()])) {
            throw new UnprocessableEntityHttpException('Username already registered');
        }

        $appearanceOptions = $this->verifiesAppearance($data);

        $character = new Character();

        $character->setEmail($data->getEmail());
        $character->setUsername($data->getUsername());
        $character->setPasswordHash(
            $this->passwordHasher->hashPassword($character, $data->getPassword()),
        );

        $character->setRace($appearanceOptions['race']);
        $character->setHair($appearanceOptions['hair']);
        $character->setEyes($appearanceOptions['eyes']);
        $character->setMouth($appearanceOptions['mouth']);
        $character->setNose($appearanceOptions['nose']);
        $character->setEars($appearanceOptions['ears']);


        $token = $this->emailVerificationService->createToken($character);

        $email = new TemplatedEmail()
            ->from($_ENV['MAILER_FROM'] ?? 'noreply@ecliptix.local')
            ->to($character->getEmail())
            ->subject('Vítej v Ecliptixu — ověř svůj účet')
            ->htmlTemplate('email/verify.html.twig')
            ->textTemplate('email/verify.txt.twig')
            ->context([
                'token' => (string) $token->getToken(),
                'username' => $character->getUsername(),
                'verify_url' => $_ENV['VERIFY_EMAIL_URL'],
            ]);

        $this->bus->dispatch(new SendEmailMessage($email));

        $entityManager = $this->entityManager;
        $entityManager->persist($character);
        $entityManager->flush();

        return new Response(status: Response::HTTP_CREATED);
    }

    /**
     * @return array <string, AppearanceOption>
     */
    private function verifiesAppearance(RegisterInput $data): array
    {

        $race = $this->raceRepository->getById($data->getRaceId());
        if ($race === null) {
            throw new UnprocessableEntityHttpException('Invalid race_id');
        }

        $options = [
            'race' => $race,
            'hair' => $this->appearanceOptionRepository->getByIdRaceType($data->getHairId(), $race, AppearanceTypeEnum::hair),
            'eyes' => $this->appearanceOptionRepository->getByIdRaceType($data->getEyesId(), $race, AppearanceTypeEnum::eyes),
            'mouth' => $this->appearanceOptionRepository->getByIdRaceType($data->getMouthId(), $race, AppearanceTypeEnum::mouth),
            'nose' => $this->appearanceOptionRepository->getByIdRaceType($data->getNoseId(), $race, AppearanceTypeEnum::nose),
            'ears' => $this->appearanceOptionRepository->getByIdRaceType($data->getEarsId(), $race, AppearanceTypeEnum::ears),
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
