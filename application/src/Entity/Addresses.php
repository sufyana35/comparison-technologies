<?php

namespace App\Entity;

use App\Repository\AddressesRepository;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressesRepository::class)]
class Addresses
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[ORM\Column(length: 100)]
    private ?string $forename = null;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[ORM\Column(length: 100)]
    private ?string $surname = null;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[ORM\Column(length: 100)]
    private ?string $line_1 = null;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[ORM\Column(length: 100)]
    private ?string $line_2 = null;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[ORM\Column(length: 50)]
    private ?string $city = null;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[ORM\Column(length: 50)]
    private ?string $county = null;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[ORM\Column(length: 15)]
    private ?string $postcode = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\OneToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Countries $country = null;

    #[ORM\ManyToOne(inversedBy: 'addresses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customers $customer = null;

    /**
     * Undocumented function
     *
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getForename(): string
    {
        return $this->forename;
    }

    /**
     * @param string $forename
     *
     * @return static
     */
    public function setForename(string $forename): static
    {
        $this->forename = $forename;

        return $this;
    }

    /**
     * @return string
     */
    public function getSurname(): string
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     *
     * @return static
     */
    public function setSurname(string $surname): static
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLine1(): string
    {
        return $this->line_1;
    }

    /**
     * @param string $line_1
     *
     * @return static
     */
    public function setLine1(string $line_1): static
    {
        $this->line_1 = $line_1;

        return $this;
    }

    /**
     * @return string
     */
    public function getLine2(): string
    {
        return $this->line_2;
    }

    /**
     * @param string|null $line_2
     *
     * @return static
     */
    public function setLine2(?string $line_2): static
    {
        $this->line_2 = $line_2;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return static
     */
    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getCounty(): string
    {
        return $this->county;
    }

    /**
     * @param string $county
     *
     * @return static
     */
    public function setCounty(string $county): static
    {
        $this->county = $county;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostcode(): string
    {
        return $this->postcode;
    }

    /**
     * @param string $postcode
     *
     * @return static
     */
    public function setPostcode(string $postcode): static
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    /**
     * @param \DateTimeInterface|null $created_at
     *
     * @return static
     */
    public function setCreatedAt(?\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    /**
     * @param \DateTimeInterface|null $updated_at
     *
     * @return static
     */
    public function setUpdatedAt(?\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * Get Country
     *
     * @return Countries
     */
    public function getCountry(): ?Countries
    {
        return $this->country;
    }

    /**
     * Set Country
     *
     * @param Countries $country
     *
     * @return static
     */
    public function setCountry(Countries $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getCustomer(): ?Customers
    {
        return $this->customer;
    }

    public function setCustomer(?Customers $customer): static
    {
        $this->customer = $customer;

        return $this;
    }
}
