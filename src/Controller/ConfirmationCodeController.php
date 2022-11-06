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
use Atournayre\Helper\Controller\Controller;
use Atournayre\Helper\Exception\TypedException;
use Atournayre\Helper\Service\FlashService;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Uid\Uuid;

class ConfirmationCodeController extends Controller
{
    public function __construct(
        LoggerInterface                               $logger,
        FlashService                                  $flashService,
        private readonly LoaderConfig                 $loaderConfig,
        protected readonly ConfirmationCodeService    $confirmationCodeService,
        protected readonly ConfirmationCodeRepository $confirmationCodeRepository,
    ) {
        parent::__construct($logger, $flashService);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ConfirmationCodeException
     */
    protected function getProvider(ConfirmationCodeDTO $confirmationCodeDTO): AbstractProvider
    {
        $providerClassName = $this->loaderConfig->getProvider($confirmationCodeDTO->type);

        if ($this->container->has($providerClassName)) return $this->container->get($providerClassName);

        throw new ServiceNotFoundException($providerClassName);
    }

    /**
     * @param string $id
     *
     * @return ConfirmationCode
     * @throws TypedException
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
