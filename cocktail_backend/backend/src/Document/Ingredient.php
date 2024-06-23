<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document]
class Ingredient 
{
    #[ODM\Id(strategy: "INCREMENT")]
    private ?int $id = null;

    #[ODM\Field(type: "string")]
    private ?string $name;

    #[ODM\Field(type: "string")]
    private ?string $type;

   

    private ?\Doctrine\ODM\MongoDB\DocumentManager $documentManager;

    public function __construct(\Doctrine\ODM\MongoDB\DocumentManager $documentManager = null)
    {
        $this->documentManager = $documentManager;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {

        return $this->name;
    }

    public function getType(): ?string
    {

        return $this->type;
    }
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

  
    }

   

