<?php

namespace Atournayre\Bundle\ConfirmationBundle\Exception;

use Atournayre\Bundle\ConfirmationBundle\DTO\ConfirmationCodeDTO;

class ConfirmationCodeUserException extends \Exception
{
    public static function noConfirmationCode(ConfirmationCodeDTO $confirmationCodeDTO): \Exception
    {
        return new static(
            sprintf(
                'The "%s" confirmation code is not valid.',
                $confirmationCodeDTO->code
            )
        );
    }

    public static function entityNotFound(string $message): \Exception
    {
        return new static($message);
    }

    public static function doNotExistOrInvalid(?\Exception $exception = null): \Exception
    {
        return new static('Confirmation request impossible due to invalid or not existing code.', $exception?->getCode(), $exception);
    }
}
