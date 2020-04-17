<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CartContentRepository")
 */
class CartContent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Cart", inversedBy="cartContents")
     */
    private $Cart;

    /**
     * @ORM\Column(type="integer")
     */
    private $Quantity;

    /**
     * @ORM\Column(type="datetime")
     */
    private $AddedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="cartContents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Product;

    public function __construct()
    {
        $this->Product = new ArrayCollection();
        $date = new \DateTime();
        $this->setAddedAt($date);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCart(): ?Cart
    {
        return $this->Cart;
    }

    public function setCart(?Cart $Cart): self
    {
        $this->Cart = $Cart;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->Quantity;
    }

    public function setQuantity(int $Quantity): self
    {
        $this->Quantity = $Quantity;

        return $this;
    }

    public function getAddedAt(): ?\DateTimeInterface
    {
        return $this->AddedAt;
    }

    public function setAddedAt(\DateTimeInterface $AddedAt): self
    {
        $this->AddedAt = $AddedAt;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->Product;
    }

    public function setProduct(?Product $Product): self
    {
        $this->Product = $Product;

        return $this;
    }
}
