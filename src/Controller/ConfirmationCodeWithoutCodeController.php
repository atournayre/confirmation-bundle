<?php

namespace Atournayre\Bundle\ConfirmationBundle\Controller;

use Atournayre\Bundle\ConfirmationBundle\DTO\ConfirmationCodeDTO;
use Atournayre\Bundle\ConfirmationBundle\Form\ConfirmationFormType;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfirmationCodeWithoutCodeController extends ConfirmationCodeController
{
    /**
     * @param Request $request
     * @param string  $mapping
     * @param string  $id
     *
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(
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
            $this->addFlash('danger', $messageErreur = 'Oops an error occurs.');
            return $this->renderError($messageErreur);
        }
    }
}
