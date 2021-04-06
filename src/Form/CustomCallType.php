<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Entity\CustomCall;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\Type\KeyValueType;

class CustomCallType extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('method', ChoiceType::class, [
                'label' => 'HTTP Method',
                'choices' => [
                    CustomCall::METHOD_GET => CustomCall::METHOD_GET,
                    CustomCall::METHOD_POST => CustomCall::METHOD_POST,
                    CustomCall::METHOD_PUT => CustomCall::METHOD_PUT,
                    CustomCall::METHOD_DELETE => CustomCall::METHOD_DELETE,
                ]
            ])
            ->add('url', TextType::class, ['label' => "URL"])
            ->add('submit', SubmitType::class, [
                'label' => 'button.save',
                'attr' => [
                    'class' => 'btn-lg btn-primary waves-effect'
                ]
            ])
            ->add('parameters', CollectionType::class, [
                'entry_type' => KeyValueType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'label' => 'Parameters',
                'entry_options' => [
                    'label' => false
                ]
            ]);

        $builder->addModelTransformer($this);
    }

    /**
     * {@inheritDoc}
     */
    public function transform($value)
    {
        $formParameters = [];

        foreach ($value->getParameters() as $key => $val) {
            $formParameters[] = [
                "key" => $key,
                "value" => $val
            ];
        }

        $value->setParameters($formParameters);
        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($value)
    {
        $objParameters = [];

        foreach ($value->getParameters() as $parameter) {
            $objParameters[$parameter['key']] = $parameter['value'];
        }

        $value->setParameters($objParameters);
        return $value;
    }
}
