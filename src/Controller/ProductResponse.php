<?php

namespace App\Controller;

use AllowDynamicProperties;
use App\Entity\Product;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AllowDynamicProperties] class ProductResponse
{
	public uuid $id;
	public ?string $name;
	public ?string $description;
	public ?float $price;
	public ?string $brand;
	public ?int $stock;
	/** @var array<string> */
	public array $links;
	public static function fromProduct(Product $product, UrlGeneratorInterface $urlGenerator): self
	{
		$response = new self();
		$response->id = $product->getId();
		$response->name = $product->getName();
		$response->description = $product->getDescription();
		$response->price = $product->getPrice();
		$response->brand = $product->getBrand();
		$response->stock = $product->getStock();
		$response->links = [
			'self' => $urlGenerator->generate('get_product', ['id' => $product->getId()]),
		];
		return $response;
	}
}
