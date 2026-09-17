<?php

namespace KikCMS\Domain\FrontendForm;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                'attr'        => ['placeholder' => 'Naam'],
                'constraints' => [new NotBlank()],
                'label'       => false
            ])
            ->add('email', EmailType::class, [
                'label'       => false,
                'attr'        => ['placeholder' => 'E-mail adres'],
                'constraints' => [new NotBlank(), new Email()]
            ])
            ->add('message', TextareaType::class, [
                'label'       => false,
                'attr'        => ['placeholder' => 'Bericht', 'rows' => 5],
                'constraints' => [new NotBlank()]
            ])
            ->add('type', ChoiceType::class, [
                'label'            => false,
                'placeholder'      => 'Pick an option',
                'placeholder_attr' => ['disabled' => true],
                'choices'          => ['Option 1' => 1, 'Option 2' => 2],
                'constraints'      => [new NotBlank()]
            ])
            ->add('checkbox', ChoiceType::class, [
                'label'       => 'Kies een opties',
                'choices'     => ['Option 1' => 1, 'Option 2' => 2, 'Option 3 long' => 3],
                'multiple'    => true,
                'expanded'    => true,
                'constraints' => [new NotBlank()],
            ])
            ->add('send', SubmitType::class, ['label' => 'Versturen'])
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}