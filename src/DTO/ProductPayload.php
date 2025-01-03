<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ProductPayload
{
	#[Assert\NotBlank(message: "The name is required.")]
	public string $name;
	#[Assert\NotBlank(message: "The description is required.")]
	public string $description;
	#[Assert\NotBlank(message: "The price is required.")]
	public float $price;
	#[Assert\NotBlank(message: "The brand is required.")]
	public string $brand;
	#[Assert\NotBlank(message: "The stock is required.")]
	public int $stock;

}
