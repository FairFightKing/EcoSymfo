<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CartRepository")
 */
class Cart
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $PurchasedAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $Status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CartContent", mappedBy="Cart")
     */
    private $cartContents;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="carts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;


    public function __construct()
    {
        $this->cartContents = new ArrayCollection();
        // set the default status to false
        $this->setStatus(false);

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPurchasedAt(): ?\DateTimeInterface
    {
        return $this->PurchasedAt;
    }

    public function setPurchasedAt(?\DateTimeInterface $PurchasedAt): self
    {
        $this->PurchasedAt = $PurchasedAt;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->Status;
    }

    public function setStatus(bool $Status): self
    {
        $this->Status = $Status;

        return $this;
    }

    /**
     * @return Collection|CartContent[]
     */
    public function getCartContents(): Collection
    {
        return $this->cartContents;
    }

    public function addCartContent(CartContent $cartContent): self
    {
        if (!$this->cartContents->contains($cartContent)) {
            $this->cartContents[] = $cartContent;
            $cartContent->setCart($this);
        }

        return $this;
    }

    public function removeCartContent(CartContent $cartContent): self
    {
        if ($this->cartContents->contains($cartContent)) {
            $this->cartContents->removeElement($cartContent);
            // set the owning side to null (unless already changed)
            if ($cartContent->getCart() === $this) {
                $cartContent->setCart(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
