<?php

namespace Atournayre\Bundle\ConfirmationBundle\Entity;

use Atournayre\Bundle\ConfirmationBundle\Repository\ConfirmationCodeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ConfirmationCodeRepository::class)]
class ConfirmationCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\Column(length: 15)]
    private ?string $type = null;

    #[ORM\Column(length: 6)]
    private ?string $code = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $targetId = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): ConfirmationCode
    {
        $this->type = $type;
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): ConfirmationCode
    {
        $this->code = $code;
        return $this;
    }

    public function getTargetId(): ?Uuid
    {
        return $this->targetId;
    }

    public function setTargetId(?Uuid $targetId): ConfirmationCode
    {
        $this->targetId = $targetId;
        return $this;
    }
}
