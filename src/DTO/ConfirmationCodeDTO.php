<?php

namespace Atournayre\Bundle\ConfirmationBundle\DTO;

use Symfony\Component\Uid\Uuid;

class ConfirmationCodeDTO
{
    public function __construct(
        public readonly Uuid   $targetId,
        public readonly string $type,
        public ?string         $code = null,
    ) {
    }
}
