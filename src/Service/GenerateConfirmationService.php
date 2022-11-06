<?php

namespace Atournayre\Bundle\ConfirmationBundle\Service;

use Atournayre\Bundle\ConfirmationBundle\Contracts\ConfirmableInterface;
use Atournayre\Bundle\ConfirmationBundle\Exception\ConfirmationCodeException;
use Atournayre\Bundle\ConfirmationBundle\Factory\ConfirmationCodeFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use ReflectionClass;
use ReflectionException;
use stdClass;
use Symfony\Component\Uid\Uuid;

class GenerateConfirmationService
{
    // TODO Remplacer par un paramètre de configuration ?
    private const  DEFAULT_NUMBERS_OF_DIGITS_FOR_CODE = 6;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NotifierService        $notifierService,
    ) {
    }

    /**
     * @throws ConfirmationCodeException
     */
    public function __invoke(Uuid $targetId, string $type): void
    {
        try {
            $this->createConfirmationCode($type, $targetId);
            $this->updateTargetedEntity($type, $targetId);
            $this->entityManager->flush();
            ($this->notifierService)($targetId, $type);
        } catch (Exception $exception) {
            throw ConfirmationCodeException::fromException($exception);
        }
    }

    /**
     * @throws Exception
     */
    private function createConfirmationCode(string $type, Uuid $targetId): void
    {
        $confirmationCode = ConfirmationCodeFactory::create($type, $targetId, self::DEFAULT_NUMBERS_OF_DIGITS_FOR_CODE);
        $this->entityManager->persist($confirmationCode);
    }

    /**
     * @param string $type
     * @param Uuid   $targetId
     *
     * @return void
     * @throws ConfirmationCodeException
     * @throws ORMException
     * @throws ReflectionException
     */
    private function updateTargetedEntity(string $type, Uuid $targetId): void
    {
        $entityName = $this->getEntityClassFromMapping($type);

        $reflectionClass = new ReflectionClass($entityName);
        $implementsInterface = $reflectionClass->implementsInterface(ConfirmableInterface::class);

        if (!$implementsInterface) {
            throw ConfirmationCodeException::mustImplementInterface($entityName);
        }

        $entity = $this->entityManager->getReference($entityName, $targetId);

        $entity->setAsNotConfirmed();

        $this->entityManager->persist($entity);
    }

    private function getEntityClassFromMapping(string $type): string
    {
        // TODO Recuperer le entity_class associé au mapping passé en paramètre
        return stdClass::class;
    }
}
