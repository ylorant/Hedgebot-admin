<?php
namespace Hedgebot\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Hedgebot\CoreBundle\Entity\CustomCall;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Hedgebot\CoreBundle\Form\Type\KeyValueType;

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
                'label' => 'Save',
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

    public function transform($data)
    {
        $formParameters = [];

        foreach($data->getParameters() as $key => $value) {
            $formParameters[] = [
                "key" => $key,
                "value" => $value
            ];
        }

        $data->setParameters($formParameters);
        return $data;
    }

    public function reverseTransform($data)
    {
        $objParameters = [];

        foreach($data->getParameters() as $parameter) {
            $objParameters[$parameter['key']] = $parameter['value'];
        }

        $data->setParameters($objParameters);
        return $data;
    }
}