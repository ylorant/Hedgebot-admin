<?php
namespace App\Modules\HedgebotHoraroBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;

class ScheduleURLType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', TextType::class, ['label' => "Schedule URL"])
            ->add('submit', SubmitType::class, [
                'label' => "Add",
                'attr' => [
                    'class' => 'btn-link waves-effect'
                ]
            ]);
    }
}
