<?php

namespace App\Message;

class AddProductMessage
{
	private string $name;
	private string $description;
	private float $price;
	private string $brand;
	private int $stock;

	public function __construct(string $name, string $description, float $price, string $brand, int $stock)
	{
		$this->name = $name;
		$this->description = $description;
		$this->price = $price;
		$this->brand = $brand;
		$this->stock = $stock;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getDescription(): string
	{
		return $this->description;
	}

	public function getPrice(): float
	{
		return $this->price;
	}

	public function getBrand(): string
	{
		return $this->brand;
	}

	public function getStock(): int
	{
		return $this->stock;
	}
}

