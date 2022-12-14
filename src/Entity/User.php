<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name:"users")]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column(type: 'integer')]
  private $id;

  #[Assert\NotBlank()]
  #[ORM\Column(type: 'string', length: 255)]
  private $firstName;
  
  #[Assert\NotBlank()]
  #[ORM\Column(type: 'string', length: 255)]
  private $lastName;
  
  #[Assert\NotBlank()]
  #[Assert\Email()]
  #[ORM\Column(type: 'string', length: 180, unique: true)]
  private $email;

  #[ORM\Column(type: 'json')]
  private $roles = [];

  #[ORM\Column(type: 'string')]
  private $password;

  #[ORM\Column(type: 'datetime_immutable', options:["default"=>"CURRENT_TIMESTAMP"])]
  private $createdAt;

  #[ORM\Column(type: 'datetime_immutable', options:["default"=>"CURRENT_TIMESTAMP"])]
  private $updatedAt;

  #[ORM\OneToMany(mappedBy: 'user', targetEntity: Pin::class, orphanRemoval: true)]
  private $pins;

  #[ORM\Column(type: 'boolean')]
  private $isVerified = false;

  public function __construct()
  {
      $this->pins = new ArrayCollection();
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getFirstName(): ?string
  {
    return $this->firstName;
  }

  public function setFirstName(string $firstName): self
  {
    $this->firstName = $firstName;

    return $this;
  }

  public function getLastName(): ?string
  {
    return $this->lastName;
  }

  public function setLastName(string $lastName): self
  {
    $this->lastName = $lastName;

    return $this;
  }

  public function getEmail(): ?string
  {
    return $this->email;
  }

  public function setEmail(string $email): self
  {
    $this->email = $email;

    return $this;
  }

  public function getCreatedAt(): ?\DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function setCreatedAt(\DateTimeImmutable $createdAt): self
  {
    $this->createdAt = $createdAt;

    return $this;
  }

  public function getUpdatedAt(): ?\DateTimeImmutable
  {
    return $this->updatedAt;
  }

  public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
  {
    $this->updatedAt = $updatedAt;

    return $this;
  }

  #[ORM\PrePersist]
  #[ORM\PreUpdate]
  public function updateTimesTamps()
  {
    if ($this->getCreatedAt() === null) {
      $this->setCreatedAt(new DateTimeImmutable);
    }
    $this->setUpdatedAt(new DateTimeImmutable);
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
   * @deprecated since Symfony 5.3, use getUserIdentifier instead
   */
  public function getUsername(): string
  {
    return (string) $this->email;
  }



  /**
   * @see UserInterface
   */
  public function getRoles(): array
  {
    $roles = $this->roles;
    // guarantee every user at least has ROLE_USER
    $roles[] = 'ROLE_USER';

    return array_unique($roles);
  }

  public function setRoles(array $roles): self
  {
    $this->roles = $roles;

    return $this;
  }

  /**
   * @see PasswordAuthenticatedUserInterface
   */
  public function getPassword(): string
  {
    return $this->password;
  }

  public function setPassword(string $password): self
  {
    $this->password = $password;

    return $this;
  }

  /**
   * Returning a salt is only needed, if you are not using a modern
   * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
   *
   * @see UserInterface
   */
  public function getSalt(): ?string
  {
    return null;
  }

  /**
   * @see UserInterface
   */
  public function eraseCredentials()
  {
    // If you store any temporary, sensitive data on the user, clear it here
    // $this->plainPassword = null;
  }

  /**
   * @return Collection<int, Pin>
   */
  public function getPins(): Collection
  {
      return $this->pins;
  }

  public function addPin(Pin $pin): self
  {
      if (!$this->pins->contains($pin)) {
          $this->pins[] = $pin;
          $pin->setUser($this);
      }

      return $this;
  }

  public function removePin(Pin $pin): self
  {
      if ($this->pins->removeElement($pin)) {
          // set the owning side to null (unless already changed)
          if ($pin->getUser() === $this) {
              $pin->setUser(null);
          }
      }

      return $this;
  }

  public function getFullName(): string
  {
      return $this->getFirstName() . ' ' . $this->getLastName();
  }

  public function getGravatarUrl(?int $size=150): string
  {
      return "https://gravatar.com/avatar/".md5(strtolower(trim($this->getEmail())))."/?s=".$size;
  }

  public function isVerified(): bool
  {
      return $this->isVerified;
  }

  public function setIsVerified(bool $isVerified): self
  {
      $this->isVerified = $isVerified;

      return $this;
  }
}
