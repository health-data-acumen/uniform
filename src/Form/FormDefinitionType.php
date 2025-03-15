<?php

namespace App\Form;

use App\Entity\FormDefinition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

class FormDefinitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var FormDefinition|null $formDefinition */
        $formDefinition = $options['data'] ?? null;

        $builder
            ->add('name', TextType::class, [
                'label' => t('label.form.name'),
            ])
            ->add('description', TextareaType::class, [
                'label' => t('label.form.description'),
                'required' => false,
            ])
        ;

        if ($formDefinition?->getId()) {
            $builder
                ->add('fields', CollectionType::class, [
                    'entry_type' => FormFieldType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FormDefinition::class,
        ]);
    }
}
