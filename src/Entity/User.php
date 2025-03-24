<?php

namespace App\Entity;

use App\Entity\Settings\AccountSettings;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    /**
     * @var Collection<int, FormDefinition>
     */
    #[ORM\OneToMany(targetEntity: FormDefinition::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $formEndpoints;

    #[ORM\OneToOne(mappedBy: 'owner', cascade: ['persist', 'remove'])]
    private ?AccountSettings $accountSettings = null;

    public function __construct()
    {
        $this->formEndpoints = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, FormDefinition>
     */
    public function getFormEndpoints(): Collection
    {
        return $this->formEndpoints;
    }

    public function addFormEndpoint(FormDefinition $formEndpoint): static
    {
        if (!$this->formEndpoints->contains($formEndpoint)) {
            $this->formEndpoints->add($formEndpoint);
            $formEndpoint->setOwner($this);
        }

        return $this;
    }

    public function removeFormEndpoint(FormDefinition $formEndpoint): static
    {
        if ($this->formEndpoints->removeElement($formEndpoint)) {
            // set the owning side to null (unless already changed)
            if ($formEndpoint->getOwner() === $this) {
                $formEndpoint->setOwner(null);
            }
        }

        return $this;
    }

    public function getAccountSettings(): ?AccountSettings
    {
        return $this->accountSettings;
    }

    public function setAccountSettings(AccountSettings $accountSettings): static
    {
        // set the owning side of the relation if necessary
        if ($accountSettings->getOwner() !== $this) {
            $accountSettings->setOwner($this);
        }

        $this->accountSettings = $accountSettings;

        return $this;
    }
}
