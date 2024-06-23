<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ODM\Document]
class Order 
{
    #[ODM\Id(strategy: "INCREMENT")]
    private ?int $id = null;

    #[ODM\Field(type: "string")]
    private ?string $status;

    #[ODM\ReferenceOne(targetDocument: Cocktail::class)]
    private ?Cocktail $cocktail;

    #[ODM\Field(type: "date")]
    #[Groups(["order"])]
    private ?\DateTimeInterface $createdAt;

    private ?\Doctrine\ODM\MongoDB\DocumentManager $documentManager;

    public function __construct(\Doctrine\ODM\MongoDB\DocumentManager $documentManager = null)
    {
        $this->documentManager = $documentManager;
        $this->createdAt = new \DateTime();
        $this->status = "In Study";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null;
    }
    

    public function getCocktail(): ?Cocktail
    {
        return $this->cocktail;
    }

    public function setCocktail(Cocktail $cocktail): self
    {
        $this->cocktail = $cocktail;
        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    #[Groups(["order"])]
    public function getFormattedCreatedAt(): string
    {
        return $this->createdAt->format('Y-m-d H:i:s');
    }
}
