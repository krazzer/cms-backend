<?php

namespace KikCMS\Domain\FrontendForm;

use KikCMS\Domain\FrontendForm\SpamBlock\SpamBlockType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactFormType extends MailFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr'        => ['placeholder' => $this->translator->trans('form.contact.name')],
                'constraints' => [new NotBlank()],
                'label'       => false
            ])
            ->add('email', EmailType::class, [
                'label'       => false,
                'attr'        => ['placeholder' => $this->translator->trans('form.contact.email')],
                'constraints' => [new NotBlank(), new Email()]
            ])
            ->add('message', TextareaType::class, [
                'label'       => false,
                'attr'        => ['placeholder' => $this->translator->trans('form.contact.message'), 'rows' => 8],
                'constraints' => [new NotBlank()]
            ])
            ->add('spamblock', SpamBlockType::class)
            ->add('send', SubmitType::class, ['label' => $this->translator->trans('form.contact.send')]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }

    public function getSubject(): string
    {
        return $this->translator->trans('form.contact.subject');
    }
}