<?php

namespace App\Entity;

use App\Repository\PollMessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PollMessageRepository::class)]
class PollMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $id_text;

    #[ORM\Column(type: 'string', length: 20)]
    private $wa_id;

    #[ORM\Column(type: 'text')]
    private $text;

    #[ORM\Column(type: 'datetime')]
    private $created;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $messagesent;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $firstmessage;

    #[ORM\Column(type: 'string', length: 255)]
    private $pollid;

    public function __construct()
    {
        $this->created = new \DateTime('now');
        $this->messagesent = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdText(): ?int
    {
        return $this->id_text;
    }

    public function setIdText(int $id_text): self
    {
        $this->id_text = $id_text;

        return $this;
    }

    public function getWaId(): ?string
    {
        return $this->wa_id;
    }

    public function setWaId(string $wa_id): self
    {
        $this->wa_id = $wa_id;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getMessagesent(): ?bool
    {
        return $this->messagesent;
    }

    public function setMessagesent(?bool $messagesent): self
    {
        $this->messagesent = $messagesent;

        return $this;
    }

    public function getFirstmessage(): ?bool
    {
        return $this->firstmessage;
    }

    public function setFirstmessage(?bool $firstmessage): self
    {
        $this->firstmessage = $firstmessage;

        return $this;
    }

    public function getPollid(): ?string
    {
        return $this->pollid;
    }

    public function setPollid(string $pollid): self
    {
        $this->pollid = $pollid;

        return $this;
    }
}
