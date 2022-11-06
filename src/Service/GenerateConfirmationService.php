<?php

namespace Atournayre\Bundle\ConfirmationBundle\Service;

use Atournayre\Bundle\ConfirmationBundle\Config\LoaderConfig;
use Atournayre\Bundle\ConfirmationBundle\Contracts\ConfirmableInterface;
use Atournayre\Bundle\ConfirmationBundle\DTO\ConfirmationCodeDTO;
use Atournayre\Bundle\ConfirmationBundle\Entity\ConfirmationCode;
use Atournayre\Bundle\ConfirmationBundle\Exception\ConfirmationCodeException;
use Atournayre\Bundle\ConfirmationBundle\Factory\ConfirmationCodeFactory;
use Atournayre\Bundle\ConfirmationBundle\Provider\AbstractProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Uid\Uuid;

class GenerateConfirmationService
{
    private const DEFAULT_NUMBERS_OF_DIGITS_FOR_CODE = 6;

    /**
     * @var AbstractProvider
     */
    private $provider;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoaderConfig           $loaderConfig,
        private readonly ContainerInterface     $container,
    ) {
    }

    /**
     * @throws ConfirmationCodeException
     */
    public function __invoke(Uuid $targetId, string $type): void
    {
        try {
            $confirmationCodeDTO = new ConfirmationCodeDTO($targetId, $type);
            $this->provider = $this->getProvider($confirmationCodeDTO);

            $confirmationCode = $this->createConfirmationCode($type, $targetId);
            $this->updateTargetedEntity($confirmationCode);
            $this->entityManager->flush();
            $this->provider->notify($confirmationCode);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface|Exception $exception) {
            throw ConfirmationCodeException::fromException($exception);
        }
    }

    /**
     * @throws Exception
     */
    private function createConfirmationCode(string $type, Uuid $targetId): ConfirmationCode
    {
        $confirmationCode = ConfirmationCodeFactory::create($type, $targetId, self::DEFAULT_NUMBERS_OF_DIGITS_FOR_CODE);
        $this->entityManager->persist($confirmationCode);
        return $confirmationCode;
    }

    /**
     * @throws ConfirmationCodeException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ORMException
     */
    private function updateTargetedEntity(ConfirmationCode $confirmationCode): void
    {
        $targetId = $confirmationCode->getTargetId();
        $entity = $this->provider->getEntity($targetId);
        $this->verifyIfEntityImplementsConfirmableInterface($entity);
        $entity = $this->entityManager->getReference(get_class($entity), $targetId);
        $entity->setAsNotConfirmed();
        $this->entityManager->persist($entity);
    }

    /**
     * @throws ConfirmationCodeException
     */
    private function verifyIfEntityImplementsConfirmableInterface(object $entity): void
    {
        $reflectionClass = new ReflectionClass($entity);
        $implementsInterface = $reflectionClass->implementsInterface(ConfirmableInterface::class);

        if ($implementsInterface) return;

        throw ConfirmationCodeException::mustImplementInterface($entity);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ConfirmationCodeException
     */
    private function getProvider(ConfirmationCodeDTO $confirmationCodeDTO): AbstractProvider
    {
        $providerClassName = $this->loaderConfig->getProvider($confirmationCodeDTO->type);

        if ($this->container->has($providerClassName)) return $this->container->get($providerClassName);

        throw new ServiceNotFoundException($providerClassName);
    }
}
