<?php

namespace Atournayre\Bundle\ConfirmationBundle\Contracts;

interface ConfirmableInterface
{
    public function isConfirmed(): bool;

    public function isNotConfirmed(): bool;

    public function setAsNotConfirmed(): void;

    public function setAsConfirmed(): void;

    public function updateAfterConfirmation(): void;
}
