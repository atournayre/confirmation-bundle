<?php

namespace Atournayre\Bundle\ConfirmationBundle\Factory;

use Atournayre\Bundle\ConfirmationBundle\Entity\ConfirmationCode;
use Exception;
use Symfony\Component\Uid\Uuid;

class ConfirmationCodeFactory
{
    private const  DEFAULT_NUMBERS_OF_DIGITS_FOR_CODE = 6;

    /**
     * @throws Exception
     */
    public static function create(
        string $type,
        Uuid $targetId,
        int $numbersOfDigitsForCode = self::DEFAULT_NUMBERS_OF_DIGITS_FOR_CODE,
    ): ConfirmationCode
    {
        return (new ConfirmationCode())
            ->setType($type)
            ->setTargetId($targetId)
            ->setCode(self::generateCode($numbersOfDigitsForCode))
            ;
    }

    /**
     * @throws Exception
     */
    private static function generateCode(int $numbersOfDigits = self::DEFAULT_NUMBERS_OF_DIGITS_FOR_CODE): int
    {
        $start = str_pad(1, $numbersOfDigits, 0);
        $end = str_pad(9, $numbersOfDigits, 9);

        return random_int($start, $end);
    }
}
