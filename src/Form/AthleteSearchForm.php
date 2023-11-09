<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AthleteSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('GET');

        $builder->add(
            'name',
            TextType::class,
            [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'name or id',
                    'title' => 'Filter routes by athlete name or id',
                    'class' => 'input name',
                ],
            ]
        );

        $builder->add(
            'search',
            SubmitType::class,
            [
                'label' => 'Search',
                'attr' => [
                    'title' => 'Search routes',
                    'class' => 'button search',
                ],
            ]
        );

        $builder->add(
            'reset',
            SubmitType::class,
            [
                'label' => 'Reset',
                'attr' => [
                    'title' => 'Reset filters',
                    'class' => 'button reset',
                ],
            ]
        );
    }

    public function getBlockPrefix()
    {
        return 'filter';
    }
}
