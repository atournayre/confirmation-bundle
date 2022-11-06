<?php

namespace Atournayre\Bundle\ConfirmationBundle\Controller;

use Atournayre\Bundle\ConfirmationBundle\DTO\ConfirmationCodeDTO;
use Atournayre\Bundle\ConfirmationBundle\Form\ConfirmationFormType;
use Atournayre\Helper\Exception\TypedException;
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

                        $this->messageSucces($provider->getConfirmedMessage());

                        $entity = $provider->getEntity($confirmationCode->getTargetId());
                        return $provider->redirectAfterConfirmation($entity);
                    } catch (TypedException $exception) {
                        $this->loggerException($exception);
                        $this->messageDepuisException($exception);
                    }
                }
                if (!$form->isValid()) {
                    $this->messageAlerteFormulaireInvalide();
                }
            }

            return $provider->renderForConfirmation($form);
        } catch (TypedException $exception) {
            $this->loggerException($exception);
            $this->messageDepuisException($exception);
            return $this->renderErreur($exception->getMessage());
        } catch (Exception $exception) {
            $this->loggerException($exception);
            $this->messageErreur($messageErreur = 'Oops an error occurs.');
            return $this->renderErreur($messageErreur);
        }
    }
}
