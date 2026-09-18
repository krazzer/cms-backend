<?php

namespace KikCMS\Domain\FrontendForm\SpamBlock;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class SpamBlockType extends AbstractType
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator
    ) {}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $session = $this->requestStack->getSession();
        $request = $this->requestStack->getCurrentRequest();

        if ( ! $request->isMethod('POST') || ! $session->has(SpamBlock::SESSION_KEY)) {
            $randomQuestionId = array_rand(SpamBlock::QUESTIONS);
            $session->set(SpamBlock::SESSION_KEY, $randomQuestionId);
        } else {
            $randomQuestionId = $session->get(SpamBlock::SESSION_KEY);
        }

        $questionText = $this->translator->trans('form.spamBlock.q' . $randomQuestionId);
        $questionText .= ' (' . $this->translator->trans('form.spamBlock.check') . ')';

        $resolver->setDefaults([
            'label'            => false,
            'placeholder'      => $questionText,
            'placeholder_attr' => ['disabled' => true],
            'choices'          => array_flip(SpamBlock::COLOR_MAP),
            'constraints'      => [new NotBlank, new SpamBlock],
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}