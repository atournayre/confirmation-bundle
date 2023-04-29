<?php

namespace Atournayre\Bundle\ConfirmationBundle\Controller;

use Atournayre\Bundle\ConfirmationBundle\DTO\ConfirmationCodeDTO;
use Atournayre\Bundle\ConfirmationBundle\Form\ConfirmationFormType;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ConfirmationCodeWithoutCodeController extends ConfirmationCodeController
{
    /**
     * @param Environment $environment
     * @param Request $request
     * @param string $mapping
     * @param string $id
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
        string  $id
    ): Response {
        try {
            $confirmationCode = $this->getConfirmationCode($id);

            $formData = new ConfirmationCodeDTO($confirmationCode->getTargetId(), $mapping);

            $provider = $this->getProvider($formData);

            $form = $this->createForm(ConfirmationFormType::class, $formData);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    try {
                        ($this->confirmationCodeService)($formData);

                        $this->addFlash('success', $provider->getConfirmedMessage());

                        $entity = $provider->getEntity($confirmationCode->getTargetId());
                        return $provider->redirectAfterConfirmation($entity);
                    } catch (Exception $exception) {
                        $this->logger->error($exception->getMessage(), $exception->getTrace());
                        $this->addFlash('danger', 'Oops an error occurs.');
                    }
                }
                if (!$form->isValid()) {
                    $this->addFlash('warning', $message ?? 'Form is invalid.');
                }
            }

            return $provider->renderForConfirmation($form);
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
