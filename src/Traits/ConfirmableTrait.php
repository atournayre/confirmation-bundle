<?php

namespace Atournayre\Bundle\ConfirmationBundle\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ConfirmableTrait
{
    #[ORM\Column(type: 'boolean', length: 15, nullable: true)]
    private ?bool $confirmed = null;

    public function isConfirmed(): bool
    {
        return (bool) $this->confirmed === true;
    }

    public function isNotConfirmed(): bool
    {
        return !$this->isConfirmed();
    }

    public function setAsNotConfirmed(): void
    {
        $this->confirmed = false;
    }

    public function setAsConfirmed(): void
    {
        $this->confirmed = true;
    }
}
