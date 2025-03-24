<?php

namespace App\Entity;

use App\Entity\Settings\NotificationSettings;
use App\Repository\FormDefinitionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FormDefinitionRepository::class)]
#[ORM\Table(name: 'forms')]
#[ORM\Index(fields: ['uid'])]
#[ORM\HasLifecycleCallbacks]
class FormDefinition
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(allowNull: true)]
    private ?string $description = null;

    #[ORM\Column(type: UuidType::NAME, length: 255)]
    private ?Uuid $uid = null;

    #[ORM\Column]
    private ?bool $enabled = true;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Url]
    private ?string $redirectUrl = null;

    /**
     * @var Collection<int, FormField>
     */
    #[ORM\OneToMany(targetEntity: FormField::class, mappedBy: 'form', orphanRemoval: true)]
    private Collection $fields;

    /**
     * @var Collection<int, FormSubmission>
     */
    #[ORM\OneToMany(targetEntity: FormSubmission::class, mappedBy: 'form', orphanRemoval: true)]
    private Collection $submissions;

    /**
     * @var Collection<int, NotificationSettings>
     */
    #[ORM\OneToMany(targetEntity: NotificationSettings::class, mappedBy: 'form', orphanRemoval: true)]
    private Collection $notificationSettings;

    #[ORM\ManyToOne(inversedBy: 'formEndpoints')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
        $this->submissions = new ArrayCollection();
        $this->notificationSettings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getUid(): ?Uuid
    {
        return $this->uid;
    }

    public function setUid(Uuid $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * @return Collection<int, FormField>
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function addField(FormField $field): static
    {
        if (!$this->fields->contains($field)) {
            $this->fields->add($field);
            $field->setForm($this);
        }

        return $this;
    }

    public function removeField(FormField $field): static
    {
        if ($this->fields->removeElement($field)) {
            // set the owning side to null (unless already changed)
            if ($field->getForm() === $this) {
                $field->setForm(null);
            }
        }

        return $this;
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if (null === $this->uid) {
            $this->uid = Uuid::v7();
        }
    }

    /**
     * @return Collection<int, FormSubmission>
     */
    public function getSubmissions(): Collection
    {
        return $this->submissions;
    }

    public function addSubmission(FormSubmission $submission): static
    {
        if (!$this->submissions->contains($submission)) {
            $this->submissions->add($submission);
            $submission->setForm($this);
        }

        return $this;
    }

    public function removeSubmission(FormSubmission $submission): static
    {
        if ($this->submissions->removeElement($submission)) {
            // set the owning side to null (unless already changed)
            if ($submission->getForm() === $this) {
                $submission->setForm(null);
            }
        }

        return $this;
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

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(?string $redirectUrl): static
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    /**
     * @return Collection<int, NotificationSettings>
     */
    public function getNotificationSettings(): Collection
    {
        return $this->notificationSettings;
    }

    public function addNotificationSetting(NotificationSettings $notificationSetting): static
    {
        if (!$this->notificationSettings->contains($notificationSetting)) {
            $this->notificationSettings->add($notificationSetting);
            $notificationSetting->setForm($this);
        }

        return $this;
    }

    public function removeNotificationSetting(NotificationSettings $notificationSetting): static
    {
        if ($this->notificationSettings->removeElement($notificationSetting)) {
            // set the owning side to null (unless already changed)
            if ($notificationSetting->getForm() === $this) {
                $notificationSetting->setForm(null);
            }
        }

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
