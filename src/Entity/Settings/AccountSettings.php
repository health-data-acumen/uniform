<?php

namespace App\Entity\Settings;

use App\Repository\Settings\AccountSettingsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AccountSettingsRepository::class)]
#[ORM\Table(name: 'account_settings')]
class AccountSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Hostname]
    private ?string $smtpHost = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Positive]
    private ?int $smtpPort = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(allowNull: true)]
    private ?string $smtpUser = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(allowNull: true)]
    private ?string $smtpPassword = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(allowNull: true)]
    private ?string $emailFromName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Email]
    private ?string $emailFromAddress = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $mailerEncryption = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSmtpHost(): ?string
    {
        return $this->smtpHost;
    }

    public function setSmtpHost(?string $smtpHost): static
    {
        $this->smtpHost = $smtpHost;

        return $this;
    }

    public function getSmtpPort(): ?int
    {
        return $this->smtpPort;
    }

    public function setSmtpPort(?int $smtpPort): static
    {
        $this->smtpPort = $smtpPort;

        return $this;
    }

    public function getSmtpUser(): ?string
    {
        return $this->smtpUser;
    }

    public function setSmtpUser(?string $smtpUser): static
    {
        $this->smtpUser = $smtpUser;

        return $this;
    }

    public function getSmtpPassword(): ?string
    {
        return $this->smtpPassword;
    }

    public function setSmtpPassword(?string $smtpPassword): static
    {
        $this->smtpPassword = $smtpPassword;

        return $this;
    }

    public function getEmailFromName(): ?string
    {
        return $this->emailFromName;
    }

    public function setEmailFromName(?string $emailFromName): static
    {
        $this->emailFromName = $emailFromName;

        return $this;
    }

    public function getEmailFromAddress(): ?string
    {
        return $this->emailFromAddress;
    }

    public function setEmailFromAddress(?string $emailFromAddress): static
    {
        $this->emailFromAddress = $emailFromAddress;

        return $this;
    }

    public function getMailerEncryption(): ?string
    {
        return $this->mailerEncryption;
    }

    public function setMailerEncryption(?string $mailerEncryption): static
    {
        $this->mailerEncryption = $mailerEncryption;

        return $this;
    }
}
