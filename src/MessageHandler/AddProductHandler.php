<?php

namespace App\MessageHandler;

use App\Entity\Product;
use App\Message\AddProductMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AddProductHandler
{
	private EntityManagerInterface $entityManager;

	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	public function __invoke(AddProductMessage $message) :Product
	{
		$product = new Product();
		$product->setName($message->getName());
		$product->setDescription($message->getDescription());
		$product->setPrice($message->getPrice());
		$product->setBrand($message->getBrand());
		$product->setStock($message->getStock());

		$this->entityManager->persist($product);
		$this->entityManager->flush();

		return $product;
	}
}
