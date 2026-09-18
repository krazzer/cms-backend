<?php

namespace KikCMS\Domain\FrontendForm;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;

readonly class FrontendFormService
{
    public function __construct(
        private RequestStack $requestStack,
        private FormFactoryInterface $formFactory,
        private MailerInterface $mailer,
        private FrontendFormFormatService $frontendFormFormatService,
        #[Autowire('%app.admin_email%')] private string $adminEmail,
        #[Autowire('%app.default_email_from%')] private string $emailFrom,
    ) {}

    public function handle(string $formClass, Request $request): FormInterface|RedirectResponse
    {
        $form = $this->formFactory->create($formClass)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formType = $form->getConfig()->getType()->getInnerType();

            if ($formType instanceof MailFormType) {
                $this->handleMailForm($form);
            }

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

    private function handleMailForm(FormInterface $form): void
    {
        $formType = $form->getConfig()->getType()->getInnerType();

        $mailData = $this->frontendFormFormatService->format($form);

        $email = new TemplatedEmail()
            ->to($this->adminEmail)
            ->subject($formType->getSubject())
            ->from($this->emailFrom)
            ->htmlTemplate('email/mailform.twig')
            ->context(['mailData' => $mailData]);

        $this->mailer->send($email);
    }
}