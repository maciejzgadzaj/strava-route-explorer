<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ManageMyRoutesForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $localRoutes = $builder->getData()['local_routes'];
        $localStarredRoutes = $builder->getData()['local_starred_routes'];

        $localRoutesIds = array_keys($localRoutes);
        $choices = array_combine($localRoutesIds, $localRoutesIds);

        $builder->add(
            'route',
            ChoiceType::class,
            [
                'choices' => $choices,
                'choice_label' => false,
                'expanded' => true,
                'multiple' => true,
                'label' => false,
                'choice_attr' => function($choiceValue, $key, $value) use ($localRoutes, $localStarredRoutes) {
                    return (
                        isset($localRoutes[$choiceValue]) && $localRoutes[$choiceValue]->isPublic()
                        || isset($localStarredRoutes[$choiceValue])
                        || !isset($localRoutes[$choiceValue]) && !isset($localStarredRoutes[$choiceValue])
                    ) ? ['checked' => 'checked'] : [];
                },
            ]
        );

        $builder->add(
            'publish',
            SubmitType::class,
            [
                'label' => 'Publish selected routes',
                'attr' => [
                    'title' => 'Publish routes',
                    'class' => 'button search',
                ],
            ]
        );
    }
}
