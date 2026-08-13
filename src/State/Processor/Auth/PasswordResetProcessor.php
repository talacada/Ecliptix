<?php

namespace App\State\Processor\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

class PasswordResetProcessor implements ProcessorInterface
{
    public function __construct(
    ) {}


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        dd($data);die();
    }
}
