<?php

namespace Atournayre\Bundle\ConfirmationBundle\Controller;

use Atournayre\Bundle\ConfirmationBundle\DTO\ConfirmationCodeDTO;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfirmationCodeWithCodeController extends ConfirmationCodeController
{
    /**
     * @param Request $request
     * @param string  $mapping
     * @param string  $id
     * @param string  $code
     *
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(
        Request $request,
        string  $mapping,
        string  $id,
        string  $code
    ): Response {
        try {
            $confirmationCode = $this->getConfirmationCode($id);

            $formData = new ConfirmationCodeDTO($confirmationCode->getTargetId(), $mapping, $code);

            $provider = $this->getProvider($formData);

            ($this->confirmationCodeService)($formData);

            $this->addFlash('success', $provider->getConfirmedMessage());

            $entity = $provider->getEntity($confirmationCode->getTargetId());
            return $provider->redirectAfterConfirmation($entity);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());
            $this->addFlash('danger', $messageErreur = 'Oops an error occurs.');
            return $this->renderError($messageErreur);
        }
    }
}
