<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
	private EntityManagerInterface $entityManager;

	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	#[Route('/api/products', name: 'get_products', methods: ['GET'])]
	public function getProducts(): JsonResponse
	{
		$products = $this->entityManager->getRepository(Product::class)->findAll();
		$data = [];
		foreach ($products as $product) {
			$data[] = [
				'id' => $product->getId(),
				'name' => $product->getName(),
				'brand' => $product->getBrand(),
				'price' => $product->getPrice(),
				'description' => $product->getDescription(),
				'stock' => $product->getStock(),
			];
		}

		return new JsonResponse($data);
	}

	#[Route('/api/products/{id}', name: 'get_product', methods: ['GET'])]
	public function getProduct(int $id): JsonResponse
	{
		$product = $this->entityManager->getRepository(Product::class)->find($id);

		if (!$product) {
			return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
		}

		$data = [
			'id' => $product->getId(),
			'name' => $product->getName(),
			'price' => $product->getPrice(),
		];

		return new JsonResponse($data);
	}

	#[Route('/api/product', name: 'add_product', methods: ['POST'])]
	public function addProduct(Request $request): JsonResponse
	{
		$data = json_decode($request->getContent(), true);

		if (!isset($data['name']) || !isset($data['description']) || !isset($data['price']) || !isset($data['brand']) || !isset($data['stock'])) {
			return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
		}

		$product = new Product();
		$product->setName($data['name']);
		$product->setDescription($data['description']);
		$product->setPrice($data['price']);
		$product->setBrand($data['brand']);
		$product->setStock($data['stock']);

		$this->entityManager->persist($product);
		$this->entityManager->flush();

		return new JsonResponse(['id' => $product->getId()], Response::HTTP_CREATED);
	}
}