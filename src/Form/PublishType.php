<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PublishType
 *
 * @package App\Form
 */
class PublishType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $stravaRoutes = $builder->getData()['strava_routes'];
        $localRoutes = $builder->getData()['local_routes'];
        $localStarredRoutes = $builder->getData()['local_starred_routes'];

        $choices = [];
        foreach ($stravaRoutes as $stravaRoute) {
            if (empty($stravaRoute->private)) {
                $choices[$stravaRoute->id] = $stravaRoute->id;
            }
        }

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
                    'title' => 'Search routes',
                    'class' => 'button search',
                ],
            ]
        );
    }
}
