<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
                    'all' => null,
                    'bike' => 1,
                    'bike · road' => '1,1',
                    'bike · mtb' => '1,2',
                    'bike · cross' => '1,3',
                    'bike · mixed' => '1,5',
                    'run' => 2,
                    'run · road' => '2,1',
                    'run · trail' => '2,4',
                    'run · mixed' => '2,5',
                ],
                'choice_attr' => [
                    'type' => ['class' => 'placeholder'],
                    'bike' => ['class' => 'optionGroup'],
                    'bike-road' => ['class' => 'optionChild'],
                    'bike-mountain' => ['class' => 'optionChild'],
                    'bike-cross' => ['class' => 'optionChild'],
                    'bike-mixed' => ['class' => 'optionChild'],
                    'run' => ['class' => 'optionGroup'],
                    'run-road' => ['class' => 'optionChild'],
                    'run-trail' => ['class' => 'optionChild'],
                    'run-mixed' => ['class' => 'optionChild'],
                ],
                'choice_label' => function ($choiceValue, $key, $value) {
                    return preg_replace('/(bike |run )/', '', $key);
                },
                'label' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'type',
                    'title' => 'Filter routes by type',
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
                    '@focus' => '$event.target.select()',
                ],
            ]
        );

        $builder->add(
            'distance_min',
            IntegerType::class,
            [
                'label' => false,
                'required' => false,
                'attr' => [
                    'type' => 'number',
                    'placeholder' => 'min',
                    'title' => 'Filter routes by minimum distance',
                    '@focus' => '$event.target.select()',
                ],
            ]
        );

        $builder->add(
            'distance_max',
            IntegerType::class,
            [
                'label' => false,
                'required' => false,
                'attr' => [
                    'type' => 'number',
                    'placeholder' => 'max',
                    'title' => 'Filter routes by maximum distance',
                    '@focus' => '$event.target.select()',
                ],
            ]
        );

        $builder->add(
            'elevation_gain_min',
            IntegerType::class,
            [
                'label' => false,
                'required' => false,
                'attr' => [
                    'type' => 'number',
                    'placeholder' => 'min',
                    'title' => 'Filter routes by minimum elevation gain',
                    '@focus' => '$event.target.select()',
                ],
            ]
        );

        $builder->add(
            'elevation_gain_max',
            IntegerType::class,
            [
                'label' => false,
                'required' => false,
                'attr' => [
                    'type' => 'number',
                    'placeholder' => 'max',
                    'title' => 'Filter routes by maximum elevation gain',
                    '@focus' => '$event.target.select()',
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
                    '@focus' => '$event.target.select()',
                ],
            ]
        );

        $builder->add(
            'starred',
            CheckboxType::class,
            [
                'label' => 'Include starred routes',
                'required' => false,
                'data' => true,
                'attr' => [
                    'title' => 'Include routes starred by athlete',
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
