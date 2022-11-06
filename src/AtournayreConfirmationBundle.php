<?php

namespace Atournayre\Bundle\ConfirmationBundle;

use Atournayre\Bundle\ConfirmationBundle\DependencyInjection\ConfirmationExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AtournayreConfirmationBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new ConfirmationExtension();
        }
        return $this->extension;
    }
}
