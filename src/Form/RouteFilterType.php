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
        $routeService = $builder->getData()['route_service'];

        $builder->setMethod('GET');

        $builder->add(
            'type',
            ChoiceType::class,
            [
                'choices' => array_merge(['all' => null], array_flip($routeService->getRouteTypes())),
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
                    'placeholder' => 'route name or id',
                    'title' => 'Filter routes by name, ID, town or segment name',
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
                ],
            ]
        );

        $builder->add(
            'ascent_min',
            IntegerType::class,
            [
                'label' => false,
                'required' => false,
                'attr' => [
                    'type' => 'number',
                    'placeholder' => 'min',
                    'title' => 'Filter routes by minimum ascent',
                ],
            ]
        );

        $builder->add(
            'ascent_max',
            IntegerType::class,
            [
                'label' => false,
                'required' => false,
                'attr' => [
                    'type' => 'number',
                    'placeholder' => 'max',
                    'title' => 'Filter routes by maximum ascent',
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

        // Tags.

        $builder->add(
            'tags',
            TextType::class,
            [
                'label' => 'Places',
                'required' => false,
                'attr' => [
                    'placeholder' => 'town names',
                ],
            ]
        );

        // Segments.

        $builder->add(
            'segments',
            TextType::class,
            [
                'label' => 'Segments',
                'required' => false,
                'attr' => [
                    'placeholder' => 'segment names or ids',
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
                ],
            ]
        );

        $builder->add(
            'start_dist',
            ChoiceType::class,
            [
                'label' => 'within',
                'choices' => array_flip($routeService->getLocationDistances()),
                'required' => false,
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
                ],
            ]
        );

        $builder->add(
            'end_dist',
            ChoiceType::class,
            [
                'label' => 'within',
                'choices' => array_flip($routeService->getLocationDistances()),
                'required' => false,
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
