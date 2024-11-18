<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
	#[ORM\Id]
	#[ORM\GeneratedValue(strategy: 'NONE')]
	#[ORM\Column(type: 'uuid')]
	private Uuid $id;

	#[ORM\Column(type: 'string', length: 255)]
	private ?string $name = null;

	#[ORM\Column(type: 'text')]
	private ?string $description = null;

	#[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
	private ?float $price = null;

	#[ORM\Column(type: 'string', length: 100)]
	private ?string $brand = null;

	#[ORM\Column(type: 'integer')]
	private ?int $stock = null;

	public function __construct()
	{
		$this->id = Uuid::v7();
	}

	public function getId(): Uuid|UuidV7
	{
		return $this->id;
	}


	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName(string $name): self
	{
		$this->name = $name;

		return $this;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setDescription(string $description): self
	{
		$this->description = $description;

		return $this;
	}

	public function getPrice(): ?float
	{
		return $this->price;
	}

	public function setPrice(float $price): self
	{
		$this->price = $price;

		return $this;
	}

	public function getBrand(): ?string
	{
		return $this->brand;
	}

	public function setBrand(string $brand): self
	{
		$this->brand = $brand;

		return $this;
	}

	public function getStock(): ?int
	{
		return $this->stock;
	}

	public function setStock(int $stock): self
	{
		$this->stock = $stock;

		return $this;
	}
}