<?php

namespace Atournayre\Bundle\ConfirmationBundle\Service;

use Atournayre\Bundle\ConfirmationBundle\Config\LoaderConfig;
use Atournayre\Bundle\ConfirmationBundle\DTO\ConfirmationCodeDTO;
use Atournayre\Bundle\ConfirmationBundle\Exception\ConfirmationCodeException;
use Atournayre\Bundle\ConfirmationBundle\Exception\ConfirmationCodeUserException;
use Atournayre\Bundle\ConfirmationBundle\Provider\AbstractProvider;
use Atournayre\Bundle\ConfirmationBundle\Repository\ConfirmationCodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class ConfirmationCodeService
{
    public function __construct(
        private readonly ContainerInterface         $container,
        private readonly ConfirmationCodeRepository $confirmationCodeRepository,
        private readonly EntityManagerInterface     $entityManager,
        private readonly LoaderConfig               $loaderConfig,
    ) {
    }

    /**
     * @param ConfirmationCodeDTO $confirmationCodeDTO
     * @return void
     * @throws ConfirmationCodeException
     */
    public function __invoke(
        ConfirmationCodeDTO $confirmationCodeDTO,
    ): void {
        $confirmationCode = $this->confirmationCodeRepository->findOneBy([
            'code' => $confirmationCodeDTO->code,
            'targetId' => $confirmationCodeDTO->targetId,
            'type' => $confirmationCodeDTO->type,
        ]);

        if (is_null($confirmationCode)) {
            throw ConfirmationCodeUserException::noConfirmationCode($confirmationCodeDTO);
        }

        $providerClassName = $this->loaderConfig->getProvider($confirmationCodeDTO->type);

        if (!$this->container->has($providerClassName)) {
            throw new ServiceNotFoundException($providerClassName);
        }

        /** @var AbstractProvider $provider */
        $provider = $this->container->get($providerClassName);

        $entity = $provider->getEntity($confirmationCode->getTargetId());

        if (is_null($entity)) {
            throw ConfirmationCodeUserException::entityNotFound($provider->getEntityNotFoundMessage());
        }

        $provider->updateEntity($entity);

        $this->entityManager->persist($entity);
        $this->entityManager->remove($confirmationCode);
        $this->entityManager->flush();
    }
}
