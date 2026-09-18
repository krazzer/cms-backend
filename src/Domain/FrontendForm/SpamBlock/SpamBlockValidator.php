<?php

namespace KikCMS\Domain\FrontendForm\SpamBlock;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SpamBlockValidator extends ConstraintValidator
{
    public function __construct(private readonly RequestStack $requestStack) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if ( ! $constraint instanceof SpamBlock) {
            return;
        }

        $session    = $this->requestStack->getSession();
        $questionId = $session->get(SpamBlock::SESSION_KEY);

        if ( ! $questionId || ! isset(SpamBlock::QUESTIONS[$questionId])) {
            $this->context->buildViolation($constraint->message)->addViolation();
            return;
        }

        if ($value !== SpamBlock::QUESTIONS[$questionId]) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}