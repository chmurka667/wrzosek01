<?php

/**
 * Registration Form type.
 */

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class RegistrationFormType.
 *
 * Defines the registration form for new users.
 */
class RegistrationFormType extends AbstractType
{
    /**
     * Build form.
     *
     * @param FormBuilderInterface $builder Form builder
     * @param array<string, mixed> $options Form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('password', PasswordType::class, [
                'mapped' => true,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'validators.password_required',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'validators.password_min_length',
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    /**
     * Configure form options.
     *
     * @param OptionsResolver $resolver Options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
