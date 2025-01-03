<?php

namespace App\Controller;

use App\DTO\ProductPayload;
use App\Entity\Product;
use App\Message\AddProductMessage;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

class ProductController extends AbstractController
{
	use HandleTrait;

	private EntityManagerInterface $entityManager;
	private PaginationService $paginationService;


	public function __construct(EntityManagerInterface $entityManager, PaginationService $paginationService, MessageBusInterface $messageBus)
	{
		$this->entityManager = $entityManager;
		$this->paginationService = $paginationService;
		$this->messageBus = $messageBus;
	}

	#[Route('/api/products', name: 'get_products', methods: ['GET'])]
	#[OA\Get(
		summary: 'Recupere une liste paginee de products.',
		tags: ['Products'],
		parameters: [
			new OA\Parameter(
				name: 'page',
				description: 'Numero de la page a recuperer.',
				in: 'query',
				schema: new OA\Schema(type: 'integer', default: 1)
			),
		],
		responses: [
			new OA\Response(
				response: 200,
				description: 'Liste paginee des products.',
				content: new OA\JsonContent(
					type: 'array',
					items: new OA\Items(ref: new Model(type: Product::class, groups: ['product
					_list']))
				)
			),
		]
	)]
	public function getProducts(Request $request, UrlGeneratorInterface $urlGenerator): JsonResponse
	{
		$query = $this->entityManager->getRepository(Product::class)->createQueryBuilder('p')->getQuery();

		$page = max((int)$request->query->get('page', 1), 1);
		$paginatedResponse = $this->paginationService->paginate(
			$query,
			$page,
			5,
			fn(Product $product) => ProductResponse::fromProduct($product, $urlGenerator)
		);

		return new JsonResponse($paginatedResponse->toArray());
	}

	#[Route('/api/products/{id}', name: 'get_product', methods: ['GET'])]
	#[OA\Get(
		summary: 'Recupere les details d\'un product.',
		tags: ['Products'],
		parameters: [
			new OA\Parameter(
				name: 'id',
				description: 'ID du product a recuperer.',
				in: 'path',
				schema: new OA\Schema(type: 'string', format: 'uuid')
			),
		],
		responses: [
			new OA\Response(
				response: 200,
				description: 'Details du product.',
				content: new OA\JsonContent(ref: new Model(type: Product::class, groups: ['product
				_detail']))
			),
			new OA\Response(
				response: 404,
				description: 'Product non trouve.',
				content: new OA\JsonContent(properties: [
					new OA\Property(property: 'error', type: 'string')
				], type: 'object')
			)
		]
	)]
	public function getProduct(Uuid $id, UrlGeneratorInterface $urlGenerator): JsonResponse
	{
		$product = $this->entityManager->getRepository(Product::class)->find($id);

		if (!$product) {
			return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
		}

		$response = ProductResponse::fromProduct($product, $urlGenerator);

		return new JsonResponse($response);

	}

	#[Route('/api/products', name: 'add_product', methods: ['POST'])]
	#[OA\Post(
		summary: 'Ajout d un product.',
		requestBody: new OA\RequestBody(
			description: 'Donnees du product a ajouter.',
			required: true,
			content: new OA\JsonContent(ref: new Model(type: ProductPayload::class))
		),
		tags: ['Products'],
		responses: [
			new OA\Response(
				response: 201,
				description: 'Product ajoute avec succes.',
				content: new OA\JsonContent(
					properties: [
						new OA\Property(property: 'id', type: 'string', format: 'uuid')
					],
					type: 'object'
				)
			),
		]
	)]
	public function addProduct(#[MapRequestPayload(validationFailedStatusCode: 400)] ProductPayload $payload): JsonResponse
	{
		/** @var Product $product */
		$product = $this->handle(new AddProductMessage(
			$payload->name,
			$payload->description,
			$payload->price,
			$payload->brand,
			$payload->stock
		));

		return new JsonResponse([
			'id' => $product->getId(),
		], Response::HTTP_CREATED);
	}

	#[Route('/api/product/{id}', name: 'delete_product', methods: ['DELETE'])]
	#[OA\Delete(
		summary: 'Supprime un product.',
		tags: ['Products'],
		parameters: [
			new OA\Parameter(
				name: 'id',
				description: 'ID du product a supprimer.',
				in: 'path',
				schema: new OA\Schema(type: 'string', format: 'uuid')
			),
		],
		responses: [
			new OA\Response(
				response: 204,
				description: 'Product supprime avec succes.'
			),
			new OA\Response(
				response: 404,
				description: 'Product non trouve.',
				content: new OA\JsonContent(properties: [
					new OA\Property(property: 'error', type: 'string')
				], type: 'object')
			)
		]
	)]
	public function deleteProduct(Uuid $id): JsonResponse
	{
		$product = $this->entityManager->getRepository(Product::class)->find($id);

		if (!$product) {
			return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
		}

		$this->entityManager->remove($product);
		$this->entityManager->flush();

		return new JsonResponse(null, Response::HTTP_NO_CONTENT);
	}
}
