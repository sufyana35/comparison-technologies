<?php

namespace App\Form;

use App\Entity\Appliances;
use App\Entity\Brands;
use App\Entity\Categories;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormAppliancesManageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', IntegerType::class, [
                'label' => 'Internal ID:',
                'attr' => [
                    'class' => 'input',
                    'maxlength' => 20
                ],
                'disabled' => true
            ])
            ->add('parentId', IntegerType::class, [
                'label' => 'Parent Appliance:',
                'attr' => [
                    'class' => 'input ui-autocomplete-input search-appliances',
                    'id' => 'search-products',
                    'name' => 'q',
                    'autocomplete' => 'off',
                    'type' => 'text',
                    'maxlength' => 60
                ],
                'required' => true,
                'empty_data' => 0
            ])
            ->add('sku', TextType::class, [
                'label' => 'SKU:',
                'attr' => [
                    'class' => 'input tiny',
                    'maxlength' => 10
                ]
            ])
            ->add('gasCouncilNumber', TextType::class, [
                'label' => 'Gas Council Number:',
                'attr' => [
                    'class' => 'input tiny',
                    'maxlength' => 30
                ],
                'required' => false,
                'empty_data' => ''
            ])
            ->add('slug', TextType::class, [
                'label' => 'URL:',
                'attr' => [
                    'class' => 'input slug',
                    'maxlength' => 255
                ]
            ])
            ->add('urlLock', CheckboxType::class, [
                'label' => 'URL Lock:',
                'required' => false,
                'attr' => [
                    'id' => 'url_lock'
                ],
                'mapped' => false,
                'data' => true
            ])
            ->add('name', TextType::class, [
                'label' => 'Name:',
                'attr' => [
                    'class' => 'input',
                    'onkeyup' => "generate_rewrite_url('slug', $(this).val())",
                    'maxlength' => 255
                ]
            ])
            ->add('searchKeywords', TextareaType::class, [
                'label' => 'Search Keywords:',
                'attr' => [
                    'id' => 'keywords',
                    'class' => 'input'
                ],
                'required' => false,
                'empty_data' => ''
            ])
            ->add('title', TextType::class, [
                'label' => 'Title:',
                'attr' => [
                    'class' => 'input',
                    'maxlength' => 255
                ]
            ])
            ->add('description', TextType::class, [
                'label' => 'Description:',
                'attr' => [
                    'class' => 'input',
                    'maxlength' => 255
                ]
            ])
            ->add('heading', TextType::class, [
                'label' => 'Heading:',
                'attr' => [
                    'class' => 'input',
                    'maxlength' => 255
                ],
                'required' => false,
                'empty_data' => ''
            ])
            ->add('body', TextareaType::class, [
                'label' => 'Body:',
                'attr' => [
                    'class' => 'mytextarea',
                ],
                'required' => false,
                'empty_data' => ''
            ])
            ->add('brand', EntityType::class, [
                'class' => Brands::class,
                'choice_label' => 'name',
            ])
            ->add('category', EntityType::class, [
                'class' => Categories::class,
                'placeholder' => 'Select a category if applicable',
                'choice_label' => function (Categories $categories) {
                    return sprintf('(%u) - %s', $categories->getId(), $categories->getName());
                }
            ])
            ->add('applianceProducts', CollectionType::class, [
                'entry_type' => FormAppliancesProductsManageType::class,
                'entry_options' => [
                    'label' => false
                ],
                'allow_add' => true,
                'prototype' => true,
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true
            ])
            ->add('updatedAt', DateTimeType::class, [
                'label' => 'Updated At:',
                'attr' => [
                    'class' => 'input wide'
                ],
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'EEEE dd MMMM YYYY h:m aaaa',
                'disabled' => true
            ])
            ->add('createdAt', DateTimeType::class, [
                'label' => 'Created At:',
                'attr' => [
                    'class' => 'input wide'
                ],
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'EEEE dd MMMM YYYY h:m aaaa',
                'disabled' => true
            ])
            ->add('activeAt', DateTimeType::class, [
                'label' => 'Active At:',
                'attr' => [
                    'class' => 'input wide'
                ],
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'EEEE dd MMMM YYYY h:m aaaa',
                'disabled' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appliances::class,
        ]);
    }
}
