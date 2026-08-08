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
use AppearanceValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
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
        private EmailVerificationService  $emailVerificationService,
        private MessageBusInterface $bus,
        private AppearanceValidationService $appearanceValidationService,
        #[Autowire(env: 'MAILER_FROM')]
        private string $mailerFrom,
        #[Autowire(env: 'VERIFY_EMAIL_URL')]
        private string $verifyEmailUrl,
    ) {
    }

    /**
     * @throws ExceptionInterface
     * @param RegisterInput $data
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

        $appearanceOptions = $this->appearanceValidationService->verifiesAppearance(
            $data->getRaceId(),
            $data->getHairId(),
            $data->getEyesId(),
            $data->getMouthId(),
            $data->getNoseId(),
            $data->getEarsId()
        );

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
            ->from($this->mailerFrom)
            ->to($character->getEmail())
            ->subject('Vítej v Ecliptixu — ověř svůj účet')
            ->htmlTemplate('email/verify.html.twig')
            ->textTemplate('email/verify.txt.twig')
            ->context([
                'token' => (string) $token->getToken(),
                'username' => $character->getUsername(),
                'verify_url' => $this->verifyEmailUrl,
            ]);

        $this->bus->dispatch(new SendEmailMessage($email));

        $entityManager = $this->entityManager;
        $entityManager->persist($character);
        $entityManager->flush();

        return new Response(status: Response::HTTP_CREATED);
    }
}
