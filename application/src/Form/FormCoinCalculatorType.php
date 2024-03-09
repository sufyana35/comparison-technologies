<?php

namespace App\Form;

use App\Model\Coins;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class FormCoinCalculatorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amountInput', TextType::class, [
                'label' => 'Amount:',
                'attr' => [
                    'class' => 'input',
                    'maxlength' => 255
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Calculate Minimum Coins Required:'
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     * 
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Coins::class,
        ]);
    }
}
