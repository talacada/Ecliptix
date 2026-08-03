<?php

namespace App\State\Processor\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\EmailVerificationTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class VerifyEmailProcessor implements ProcessorInterface
{
    public function __construct(
        private EmailVerificationTokenRepository $emailVerificationTokenRepository,
        private EntityManagerInterface $entityManager,
    ) { }
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $dbToken = $this->emailVerificationTokenRepository->getToken($data);

        if ($dbToken === null) {
            throw new UnprocessableEntityHttpException('Invalid or expired token');
        }

        $dbToken->setUsedAt(new DateTimeImmutable('now'));
        $dbToken->getCharacter()->setEmailVerified(true);

        $this->entityManager->flush();

        return new Response(status: Response::HTTP_OK,);
    }
}
