<?php

namespace Atournayre\Bundle\ConfirmationBundle\Service;

use Symfony\Component\Uid\Uuid;

class NotifierService
{
    public function __invoke(Uuid $entityId, string $type): void
    {
        // TODO déclencher une notification en fonction du provider fourni dans la configuration
    }
}
