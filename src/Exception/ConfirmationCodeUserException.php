<?php

namespace Atournayre\Bundle\ConfirmationBundle\Exception;

use Atournayre\Bundle\ConfirmationBundle\DTO\ConfirmationCodeDTO;
use Atournayre\Helper\Exception\TypedException;

class ConfirmationCodeUserException extends TypedException
{
    public static function noConfirmationCode(ConfirmationCodeDTO $confirmationCodeDTO): TypedException
    {
        $throwable = new static(
            sprintf(
                'The "%s" confirmation code is not valid.',
                $confirmationCodeDTO->code
            )
        );
        return parent::createAsError($throwable);
    }

    public static function entityNotFound(string $message): TypedException
    {
        return new static($message);
    }

    public static function doNotExistOrInvalid(): TypedException
    {
        $throwable = new static('Confirmation request impossible due to invalid or not existing code.');
        return parent::createAsWarning($throwable);
    }
}
