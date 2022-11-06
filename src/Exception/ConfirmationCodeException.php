<?php

namespace Atournayre\Bundle\ConfirmationBundle\Exception;

use Atournayre\Bundle\ConfirmationBundle\Contracts\ConfirmableInterface;
use Atournayre\Bundle\ConfirmationBundle\DTO\ConfirmationCodeDTO;
use Exception;

class ConfirmationCodeException extends Exception
{
    public static function mustImplementInterface(string $entityName): static
    {
        return new static(sprintf('%s must implements %s', $entityName, ConfirmableInterface::class));
    }

    public static function fromException(Exception $exception): static
    {
        return new static($exception->getMessage(), $exception->getCode(), $exception);
    }

    public static function wrongVerification(ConfirmationCodeDTO $confirmationCodeDTO): static
    {
        return new static(
            sprintf(
                '"%s" is not valid for id "%s" and type "%s".',
                $confirmationCodeDTO->code,
                $confirmationCodeDTO->targetId,
                $confirmationCodeDTO->type
            )
        );
    }

    public static function entityClassNotDefined(string $mapping): static
    {
        return new static(sprintf('entity_class option is not defined for mapping "%s".', $mapping));
    }

    public static function classNotFound(mixed $entityClass): static
    {
        return new static(sprintf('Unable to find "%s".', $entityClass));
    }
}
