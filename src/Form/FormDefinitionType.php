<?php

namespace App\Form;

use App\Entity\FormDefinition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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
        ;

        if ($formDefinition?->getId()) {
            $builder
                ->add('enabled', CheckboxType::class, [
                    'label' => t('label.form_endpoint.enabled'),
                    'required' => false,
                ])
                ->add('redirectUrl', UrlType::class, [
                    'label' => t('label.form_endpoint.redirect_url'),
                    'help' => t('help.form_endpoint.redirect_url'),
                    'required' => false,
                ])
                /*
                ->add('fields', CollectionType::class, [
                    'entry_type' => FormFieldType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                ])
                */
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
