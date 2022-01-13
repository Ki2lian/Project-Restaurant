<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'error_bubbling' => true,
                'empty_data' => '', // because If we send it no data, it returns null
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a firstname',
                    ]),
                    new Regex([
                        'pattern' => "/\d/",
                        "match" => false,
                        "message" => "Your firstname cannot contain a number"
                    ])
                ],
            ])
            ->add('lastname', TextType::class, [
                'error_bubbling' => true,
                'empty_data' => '', // because If we send it no data, it returns null
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a lastname',
                    ]),
                    new Regex([
                        'pattern' => "/\d/",
                        "match" => false,
                        "message" => "Your lastname cannot contain a number"
                    ])
                ],
            ])
            ->add('email', EmailType::class, [
                'error_bubbling' => true,
                'empty_data' => '', // because If we send it no data, it returns null
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an email',
                    ])
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'error_bubbling' => true,
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('restaurateur', CheckboxType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'error_bubbling' => true,
                'empty_data' => '', // because If we send it no data, it returns null
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Regex([
                        "pattern" => '/^(?=\P{Ll}*\p{Ll})(?=\P{Lu}*\p{Lu})(?=\P{N}*\p{N})(?=[\p{L}\p{N}]*[^\p{L}\p{N}])[\s\S]{8,4096}$/',
                        "match" => true,
                        "message" => "Passwords must contain:
                        a minimum of 1 lower case letter, 
                        a minimum of 1 upper case letter, 
                        a minimum of 1 special character and 
                        your password should be at least 8 characters."
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
