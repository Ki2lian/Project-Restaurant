<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'error_bubbling' => true,
                'empty_data' => '', // because If we send it no data, it returns null and null is not type string
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a name',
                    ]),
                    new Regex([
                        "pattern" => '/^[A-zÀ-ÖØ-öø-ÿ0-9]+$/',
                        "match" => true,
                        "message" => "Special characters are not allowed in the name"
                    ])
                ],
            ])
            ->add('price', NumberType::class, [
                'error_bubbling' => true,
                'empty_data' => '', // because If we send it no data, it returns null
                'invalid_message' => 'The price must be a positive number with a maximum of 2 digits after the decimal point',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a price',
                    ]),
                    new Regex([
                        "pattern" => '/^(?=.*[1-9])[0-9]*[.,]?[0-9]{1,2}$/',
                        "match" => true,
                        "message" => "The price must be a positive number with a maximum of 2 digits after the decimal point"
                    ])
                ],
            ])
            ->add('description', TextType::class, [
                'error_bubbling' => true,
                'required' => false,
                'empty_data' => '', // because If we send it no data, it returns null
                'constraints' => [
                    new Regex([
                        "pattern" => '/^[A-zÀ-ÖØ-öø-ÿ0-9]+$/',
                        "match" => true,
                        "message" => "Special characters are not allowed in the description"
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
