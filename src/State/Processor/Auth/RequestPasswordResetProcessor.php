<?php

declare(strict_types=1);

namespace App\State\Processor\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Auth\RequestPasswordResetInput;
use App\Entity\PasswordResetToken;
use App\Repository\Character\CharacterRepository;
use App\Repository\PasswordResetTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @implements ProcessorInterface<RequestPasswordResetInput, JsonResponse>
 */
readonly class RequestPasswordResetProcessor implements ProcessorInterface
{
    public function __construct(
        private CharacterRepository $characterRepository,
        private PasswordResetTokenRepository $passwordResetTokenRepository,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
        #[Autowire(env: 'MAILER_FROM')]
        private string $mailerFrom,
        #[Autowire(env: 'VERIFY_EMAIL_URL')]
        private string $resetPasswordUrl,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $emailAddress = $data instanceof RequestPasswordResetInput ? $data->getEmail() : ($data['email'] ?? '');
        $character = $this->characterRepository->getCharacterByEmail($emailAddress);

        if ($character === null) {
            return new JsonResponse(
                ['message' => 'If the email exists, a reset link has been sent.'],
                Response::HTTP_OK
            );
        }

        $oldToken = $this->passwordResetTokenRepository->getByCharacter($character);

        // When will add created_at will check if oldToken is older than 1min
        if ($oldToken !== null) {
            $this->entityManager->remove($oldToken);
            $this->entityManager->flush();
        }

        $newToken = new PasswordResetToken();
        $newToken->setCharacter($character);
        $newToken->setExpiresAt(new DateTimeImmutable('now + 1hours'));
        $newToken->setToken(Uuid::v4());
        $newToken->setUsedAt(null);

        $this->entityManager->persist($newToken);
        $this->entityManager->flush();

        $email = new TemplatedEmail()
            ->from($this->mailerFrom)
            ->to($character->getEmail())
            ->subject('Ecliptix — Password Reset')
            ->htmlTemplate('email/reset_password.html.twig')
            ->textTemplate('email/reset_password.txt.twig')
            ->context([
                'token' => (string) $newToken->getToken(),
                'username' => $character->getUsername(),
                'reset_url' => $this->resetPasswordUrl,
            ]);

        $this->bus->dispatch(new SendEmailMessage($email));

        return new JsonResponse(
            ['message' => 'If the email exists, a reset link has been sent.'],
            Response::HTTP_OK
        );
    }
}

