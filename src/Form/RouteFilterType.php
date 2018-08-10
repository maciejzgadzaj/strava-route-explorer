<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class RouteFilterType
 *
 * @package App\Form
 */
class RouteFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('GET');

        $builder->add(
            'type',
            ChoiceType::class,
            [
                'choices' => [
                    'type' => null,
                    'bike' => 1,
                    'run' => 2,
                ],
                'choice_attr' => [
                    'type' => [
                        'class' => 'placeholder',
                    ],
                ],
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'type',
                    'title' => 'Route type',
                ],
            ]
        );

        $builder->add(
            'name',
            TextType::class,
            [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'name or id',
                    'title' => 'Filter routes by name or ID',
                    'accesskey' => 'r',
                    '@focus' => '$event.target.select()',
                ],
            ]
        );

        $builder->add(
            'distance_min',
            TextType::class,
            [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'min',
                    'title' => 'Filter routes by minimum distance',
                    'accesskey' => 'd',
                    '@focus' => '$event.target.select()',
                ],
            ]
        );

        $builder->add(
            'distance_max',
            TextType::class,
            [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'max',
                    'title' => 'Filter routes by maximum distance',
                ],
            ]
        );

        $builder->add(
            'elevation_gain_min',
            TextType::class,
            [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'min',
                    'title' => 'Filter routes by minimum elevation gain',
                    'accesskey' => 'e',
                    '@focus' => '$event.target.select()',
                ],
            ]
        );

        $builder->add(
            'elevation_gain_max',
            TextType::class,
            [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'max',
                    'title' => 'Filter routes by maximum elevation gain',
                ],
            ]
        );

        $builder->add(
            'athlete',
            TextType::class,
            [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'name or id',
                    'title' => 'Filter routes by athlete name or id',
                    'accesskey' => 'a',
                    '@focus' => '$event.target.select()',
                ],
            ]
        );

        // Start location.

        $builder->add(
            'start',
            TextType::class,
            [
                'label' => 'Start',
                'required' => false,
                'attr' => [
                    'placeholder' => 'name or address',
                    '@focus' => '$event.target.select()',
                ],
            ]
        );

        $builder->add(
            'start_dist',
            ChoiceType::class,
            [
                'label' => 'within',
                'choices' => [
                    '100 m' => 0.1,
                    '500 m' => 0.5,
                    '1 km' => 1,
                    '5 km' => 5,
                    '10 km' => 10,
                    '20 km' => 20,
                    '50 km' => 50,
                    '100 km' => 100,
                ],
                'required' => false,
                'attr' => [
                    '@focus' => '$event.target.select()',
                ],
            ]
        );

        $builder->add(
            'start_latlon',
            HiddenType::class
        );

        // End location.

        $builder->add(
            'end',
            TextType::class,
            [
                'label' => 'End',
                'required' => false,
                'attr' => [
                    'placeholder' => 'name or address',
                    '@focus' => '$event.target.select()',
                ],
            ]
        );

        $builder->add(
            'end_dist',
            ChoiceType::class,
            [
                'label' => 'within',
                'choices' => [
                    '100 m' => 0.1,
                    '500 m' => 0.5,
                    '1 km' => 1,
                    '5 km' => 5,
                    '10 km' => 10,
                    '20 km' => 20,
                    '50 km' => 50,
                    '100 km' => 100,
                ],
                'required' => false,
                'attr' => [
                    '@focus' => '$event.target.select()',
                ],
            ]
        );

        $builder->add(
            'end_latlon',
            HiddenType::class
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'filter';
    }
}
