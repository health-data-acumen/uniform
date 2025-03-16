<?php

namespace App\Form\Settings;

use App\Entity\Settings\AccountSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

class AccountSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('smtpHost', TextType::class, [
                'label' => t('label.admin.account_settings.smtp_host'),
                'required' => false,
            ])
            ->add('smtpPort', NumberType::class, [
                'label' => t('label.admin.account_settings.smtp_port'),
                'required' => false,
            ])
            ->add('smtpUser', TextType::class, [
                'label' => t('label.admin.account_settings.smtp_user'),
                'required' => false,
            ])
            ->add('smtpPassword', TextType::class, [
                'label' => t('label.admin.account_settings.smtp_password'),
                'required' => false,
            ])
            ->add('emailFromName', TextType::class, [
                'label' => t('label.admin.account_settings.email_from_name'),
                'required' => false,
            ])
            ->add('emailFromAddress', TextType::class, [
                'label' => t('label.admin.account_settings.email_from_address'),
                'required' => false,
            ])
            ->add('mailerEncryption', ChoiceType::class, [
                'label' => t('label.admin.account_settings.mailer_encryption'),
                'required' => true,
                'choices' => [
                    'None' => null,
                    'SSL' => 'ssl',
                    'TLS' => 'tls',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AccountSettings::class,
        ]);
    }
}
