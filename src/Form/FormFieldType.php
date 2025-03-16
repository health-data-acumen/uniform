<?php

namespace App\Form;

use App\Entity\FormField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

class FormFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => t('label.form_field.name'),
            ])
            ->add('label', TextType::class, [
                'label' => t('label.form_field.label'),
            ])
            ->add('type', TextType::class, [
                'label' => t('label.form_field.type'),
            ])
            ->add('required', CheckboxType::class, [
                'label' => t('label.form_field.required'),
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FormField::class,
        ]);
    }
}
