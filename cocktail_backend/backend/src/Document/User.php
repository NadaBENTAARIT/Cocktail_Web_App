<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ODM\Document]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ODM\Id(strategy: "INCREMENT")]
    private ?int $id = null;

    #[ODM\Field(type: "string")]
    private ?string $email;

    #[ODM\Field(type: "string")]
    private ?string $password;

    #[ODM\Field(type: "collection")]
    private array $roles = [];

    private ?\Doctrine\ODM\MongoDB\DocumentManager $documentManager;

    public function __construct(\Doctrine\ODM\MongoDB\DocumentManager $documentManager = null)
    {
        $this->documentManager = $documentManager;
        $this->roles = ['ROLE_USER'];
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
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

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;}
    public function eraseCredentials(): void
    {
    }
}
