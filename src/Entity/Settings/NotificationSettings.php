<?php

namespace App\Entity\Settings;

use App\Entity\FormDefinition;
use App\Repository\Settings\NotificationSettingsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NotificationSettingsRepository::class)]
#[ORM\Table(name: 'form_notification_settings')]
class NotificationSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $enabled = null;

    #[ORM\Column(length: 32)]
    #[Assert\NotBlank]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(allowNull: true)]
    private ?string $target = null;

    #[ORM\Column]
    private array $options = [];

    #[ORM\ManyToOne(inversedBy: 'notificationSettings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FormDefinition $form = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(?string $target): static
    {
        $this->target = $target;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(string $name, mixed $default = null): mixed
    {
        return $this->options[$name] ?? $default;
    }

    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function setOption(string $name, mixed $value): static
    {
        $this->options[$name] = $value;

        return $this;
    }

    public function removeOption(string $name): static
    {
        unset($this->options[$name]);

        return $this;
    }

    public function getForm(): ?FormDefinition
    {
        return $this->form;
    }

    public function setForm(?FormDefinition $form): static
    {
        $this->form = $form;

        return $this;
    }
}
