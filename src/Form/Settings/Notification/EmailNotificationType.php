<?php

namespace App\Form\Settings\Notification;

use App\Entity\Settings\NotificationSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

use function Symfony\Component\Translation\t;

class EmailNotificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enabled', CheckboxType::class, [
                'label' => t('label.admin.settings.email_notification.enabled'),
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => t('label.admin.settings.email_notification.target_email'),
                'required' => false,
                'property_path' => 'target',
                'constraints' => [
                    new Assert\NotBlank(allowNull: true),
                    new Assert\Email(),
                ],
            ])
            /*
            ->add('template', TextareaType::class, [
                'label' => t('label.admin.settings.email_notification.template'),
                'required' => false,
                'attr' => [
                    'rows' => 10,
                ],
                'getter' => fn (FormNotificationSettings $settings) => $settings->getOption('template'),
                'setter' => fn (FormNotificationSettings $settings, ?string $value) => $settings->setOption('template', $value),
                'constraints' => [
                    new Assert\NotBlank(allowNull: true),
                ],
            ])
            */
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => NotificationSettings::class,
                'label' => t('message.admin.settings.email_notification.label'),
                'help' => t('message.admin.settings.email_notification.help'),
            ])
        ;
    }
}
