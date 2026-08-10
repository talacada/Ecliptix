<?php

namespace App\State\Processor\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

class RequestPasswordResetProcessor implements ProcessorInterface
{

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        dd($data);die();
/*
1. validate he do not have any token, if has delete it
2. Create token, if was not created in last minute
3. Send email with token
4. Send generic response   "message": "Password has been reset.", even if user not exist
*/
    }
}
