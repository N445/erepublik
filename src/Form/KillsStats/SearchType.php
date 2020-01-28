<?php

namespace App\Form\KillsStats;

use App\Model\KillsStats\Search;
use App\Utils\MondayHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cookie')
//            ->add('profiles', TextareaType::class)
            ->add('semaine', ChoiceType::class, [
                'choices' => [
                    'Cette semaine'      => MondayHelper::NEXT_MONDAY,
                    'Semaine précédente' => MondayHelper::PREV_MONDAY,
                ],
            ])
          /*  ->add('file', FileType::class, [
                'mapped'   => false,
                'required' => false,
                'help'     => 'Si non remplis les données en mémoire serons utilisé',
            ])*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
        ]);
    }
}
