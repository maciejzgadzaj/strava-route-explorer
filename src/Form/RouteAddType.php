<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class RouteAddType
 *
 * @package App\Form
 */
class RouteAddType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'route_id',
            TextType::class,
            [
                'label' => false,
                'attr' => [
                    'placeholder' => 'add new route',
                    'title' => 'Route ID',
                    'accesskey' => 'n',
                    '@focus' => '$event.target.select()',
                ],
            ]
        );
    }
}
