<?php

namespace Atournayre\Bundle\ConfirmationBundle\Provider;

use Atournayre\Bundle\ConfirmationBundle\Contracts\ConfirmableInterface;
use Atournayre\Bundle\ConfirmationBundle\Entity\ConfirmationCode;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Uid\Uuid;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

abstract class AbstractProvider
{
    public function __construct(
        protected Environment     $environment,
        protected RouterInterface $router,
    ) {
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function renderForMessage(): string
    {
        $render = $this->environment->render('@AtournayreConfirmation/message.html.twig');
        return new Response($render);
    }

    /**
     * @param FormInterface $form
     *
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderForConfirmation(
        FormInterface $form,
    ): Response
    {
        $render = $this->environment->render('@AtournayreConfirmation/confirmation.html.twig', [
            'form' => $form->createView(),
        ]);
        return new Response($render);
    }

    public function updateEntity(ConfirmableInterface $entity): ConfirmableInterface
    {
        $entity->setAsConfirmed();
        if ($entity->isConfirmed()) {
            $entity->updateAfterConfirmation();
        }
        return $entity;
    }

    abstract public function redirectAfterConfirmation(object $entity): RedirectResponse;

    abstract public function getEntity(int|string|Uuid $confirmationCodeTargetId): ?ConfirmableInterface;

    abstract public function getEntityNotFoundMessage(): string;

    abstract public function getConfirmedMessage(): string;

    abstract public function notify(ConfirmationCode $confirmationCode): string;
}
