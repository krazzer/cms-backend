<?php

namespace KikCMS\Domain\FrontendForm;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class FrontendFormService
{
    public function __construct(
        private RequestStack $requestStack,
        private FormFactoryInterface $formFactory
    ) {}

    public function handle(string $formClass, Request $request): FormInterface|RedirectResponse
    {
        $form = $this->formFactory->create($formClass)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->requestStack->getSession()->set($this->getSuccessSessionKey($form), true);
            return new RedirectResponse($request->getUri());
        }

        return $form;
    }

    public function checkFormSuccess(FormInterface $form): bool
    {
        $formSessionKey = $this->getSuccessSessionKey($form);

        if ($success = $this->requestStack->getSession()->get($formSessionKey)) {
            $this->requestStack->getSession()->remove($formSessionKey);
        }

        return (bool) $success;
    }

    public function getSuccessSessionKey(FormInterface $form): string
    {
        return 'form_success_' . $form->getName();
    }
}