<?php

namespace Atournayre\Bundle\ConfirmationBundle\Controller;

use Atournayre\Bundle\ConfirmationBundle\Config\LoaderConfig;
use Atournayre\Bundle\ConfirmationBundle\DTO\ConfirmationCodeDTO;
use Atournayre\Bundle\ConfirmationBundle\Entity\ConfirmationCode;
use Atournayre\Bundle\ConfirmationBundle\Exception\ConfirmationCodeException;
use Atournayre\Bundle\ConfirmationBundle\Exception\ConfirmationCodeUserException;
use Atournayre\Bundle\ConfirmationBundle\Provider\AbstractProvider;
use Atournayre\Bundle\ConfirmationBundle\Repository\ConfirmationCodeRepository;
use Atournayre\Bundle\ConfirmationBundle\Service\ConfirmationCodeService;
use Exception;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

class ConfirmationCodeController extends AbstractController
{
    private RewindableGenerator $providers;

    public function __construct(
        protected readonly LoggerInterface            $logger,
        private readonly LoaderConfig                 $loaderConfig,
        protected readonly ConfirmationCodeService    $confirmationCodeService,
        protected readonly ConfirmationCodeRepository $confirmationCodeRepository,
        #[TaggedIterator('atournayre.confirmation_bundle.tag.provider')] RewindableGenerator $providers,
    )
    {
        $this->providers = $providers;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ConfirmationCodeException
     */
    protected function getProvider(ConfirmationCodeDTO $confirmationCodeDTO): AbstractProvider
    {
        $providerClassName = $this->loaderConfig->getProvider($confirmationCodeDTO->type);

        try {
            $providers = array_filter(
                iterator_to_array($this->providers->getIterator()),
                fn($provider) => get_class($provider) === $providerClassName
            );
        } catch (Exception $exception) {
            throw new ServiceNotFoundException($providerClassName, null, $exception);
        }

        if (count($providers) === 1) {
            return current($providers);
        }

        throw new ServiceNotFoundException($providerClassName);
    }

    /**
     * @param string $id
     * @return ConfirmationCode
     * @throws Exception
     */
    protected function getConfirmationCode(string $id): ConfirmationCode
    {
        try {
            $confirmationCode = $this->confirmationCodeRepository->find(Uuid::fromString($id));
            if (is_null($confirmationCode)) {
                throw ConfirmationCodeUserException::doNotExistOrInvalid();
            }
            return $confirmationCode;
        } catch (Exception $exception) {
            throw ConfirmationCodeUserException::doNotExistOrInvalid();
        }
    }
}
