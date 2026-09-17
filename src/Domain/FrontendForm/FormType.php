<?php

namespace KikCMS\Domain\FrontendForm;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class FormType extends AbstractType
{
    public function __construct(
        private readonly FrontendFormService $frontendFormService,
    ) {}

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['form_success'] = $this->frontendFormService->checkFormSuccess($form);
    }
}