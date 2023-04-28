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
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ErrorController;
use Symfony\Component\Uid\Uuid;

class ConfirmationCodeController extends AbstractController
{
    protected const ERROR_CONTROLLER_ACTION = ErrorController::class . '::__invoke';

    public function __construct(
        protected readonly LoggerInterface            $logger,
        private readonly LoaderConfig                 $loaderConfig,
        protected readonly ConfirmationCodeService    $confirmationCodeService,
        protected readonly ConfirmationCodeRepository $confirmationCodeRepository,
    )
    {
    }

    protected function renderError(string $errorMessage): Response
    {
        return $this->forward(self::ERROR_CONTROLLER_ACTION, [
            'exception' => new Exception($errorMessage),
        ]);
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
