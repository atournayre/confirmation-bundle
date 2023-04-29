<?php

namespace Atournayre\Bundle\ConfirmationBundle\Controller;

use Atournayre\Bundle\ConfirmationBundle\DTO\ConfirmationCodeDTO;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ConfirmationCodeWithCodeController extends ConfirmationCodeController
{
    /**
     * @param Environment $environment
     * @param Request $request
     * @param string $mapping
     * @param string $id
     * @param string $code
     *
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(
        Environment $environment,
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
            $this->addFlash('danger', $errorMessage = 'Oops an error occurs.');
            $render = $environment->render('@AtournayreConfirmation/error.html.twig', [
                'message' => $errorMessage,
            ]);
            return new Response($render);
        }
    }
}
