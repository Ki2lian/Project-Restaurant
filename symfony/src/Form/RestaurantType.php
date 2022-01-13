<?php

namespace App\Form;

use App\Entity\Restaurant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RestaurantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'error_bubbling' => true,
                'empty_data' => '', // because If we send it no data, it returns null
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a name',
                    ])
                ],
            ])
            ->add('address', TextType::class, [
                'error_bubbling' => true,
                'empty_data' => '', // because If we send it no data, it returns null
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an address',
                    ])
                ],
            ])
            ->add('phone', TextType::class, [
                'error_bubbling' => true,
                'empty_data' => '', // because If we send it no data, it returns null
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a phone number',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Restaurant::class,
        ]);
    }
}
