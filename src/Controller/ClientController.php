<?php

namespace App\Controller;

use App\DTO\ClientPayload;
use App\Entity\Client;
use App\Message\AddClientMessage;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

class ClientController extends AbstractController
{
	use HandleTrait;

	private EntityManagerInterface $entityManager;
	private PaginationService $paginationService;
	private UrlGeneratorInterface $urlGenerator;


	public function __construct(EntityManagerInterface $entityManager, PaginationService $paginationService, UrlGeneratorInterface $urlGenerator, MessageBusInterface $messageBus)
	{
		$this->entityManager = $entityManager;
		$this->paginationService = $paginationService;
		$this->urlGenerator = $urlGenerator;
		$this->messageBus = $messageBus;

	}


	#[Route('/api/clients', name: 'get_clients', methods: ['GET'])]
	#[OA\Get(
		summary: 'Recupere une liste paginee de clients.',
		tags: ['Clients'],
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
				description: 'Liste paginee des clients.',
				content: new OA\JsonContent(
					type: 'array',
					items: new OA\Items(ref: new Model(type: Client::class, groups: ['client_list']))
				)
			),
		]
	)]
	public function getClients(#[MapQueryParameter] int $page = 1): JsonResponse
	{
		$query = $this->entityManager->getRepository(Client::class)->createQueryBuilder('c')->getQuery();
		$paginatedResponse = $this->paginationService->paginate(
			$query,
			$page,
			5,
			fn(Client $client) => ClientResponse::fromClient($client, $this->urlGenerator)
		);

		return new JsonResponse($paginatedResponse->toArray());

	}

	#[Route('/api/clients/{id}', name: 'get_client', methods: ['GET'])]
	#[OA\Get(
		summary: 'Recupere les details d\'un client.',
		tags: ['Clients'],
		parameters: [
			new OA\Parameter(
				name: 'id',
				description: 'ID du client a recuperer.',
				in: 'path',
				schema: new OA\Schema(type: 'string', format: 'uuid')
			),
		],
		responses: [
			new OA\Response(
				response: 200,
				description: 'Details du client.',
				content: new OA\JsonContent(ref: new Model(type: Client::class, groups: ['client_detail']))
			),
			new OA\Response(
				response: 404,
				description: 'Client non trouve.',
				content: new OA\JsonContent(properties: [
					new OA\Property(property: 'error', type: 'string')
				], type: 'object')
			)
		]
	)]
	public function getClient(Uuid $id): JsonResponse
	{
		$client = $this->entityManager->getRepository(Client::class)->find($id);
		if (!$client) {
			return new JsonResponse(['error' => 'Client not found'], Response::HTTP_NOT_FOUND);
		}
		$response = ClientResponse::fromClient($client, $this->urlGenerator);

		return new JsonResponse($response);
	}


	#[Route('/api/client', name: 'add_client', methods: ['POST'])]
	#[OA\Post(
		summary: 'Ajoute un client.',
		requestBody: new OA\RequestBody(
			description: 'Donnees du client a ajouter.',
			required: true,
			content: new OA\JsonContent(ref: new Model(type: ClientPayload::class))
		),
		tags: ['Clients'],
		responses: [
			new OA\Response(
				response: 201,
				description: 'Client ajoute avec succes.',
				content: new OA\JsonContent(
					properties: [
						new OA\Property(property: 'id', type: 'string', format: 'uuid')
					],
					type: 'object'
				)
			),
		]
	)]
	public function addClient(#[MapRequestPayload] ClientPayload $payload): JsonResponse
	{

		/** @var Client $client */
		$client = $this->handle(new AddClientMessage($payload->name));


		return new JsonResponse([
			'id' => $client->getId(),
		], Response::HTTP_CREATED);
	}

	#[
		Route('/api/client/{id}', name: 'delete_client', methods: ['DELETE'])]
	#[OA\Delete(
		summary: 'Supprime un client.',
		tags: ['Clients'],
		parameters: [
			new OA\Parameter(
				name: 'id',
				description: 'ID du client a supprimer.',
				in: 'path',
				schema: new OA\Schema(type: 'string', format: 'uuid')
			),
		],
		responses: [
			new OA\Response(
				response: 204,
				description: 'Client supprime avec succes.'
			),
			new OA\Response(
				response: 404,
				description: 'Client non trouve.',
				content: new OA\JsonContent(properties: [
					new OA\Property(property: 'error', type: 'string')
				], type: 'object')
			)
		]
	)]
	public function deleteClient(Uuid $id): JsonResponse
	{
		$client = $this->entityManager->getRepository(Client::class)->find($id);

		if (!$client) {
			return new JsonResponse(['error' => 'Client not found'], Response::HTTP_NOT_FOUND);
		}

		$this->entityManager->remove($client);
		$this->entityManager->flush();

		return new JsonResponse(null, Response::HTTP_NO_CONTENT);
	}
}
