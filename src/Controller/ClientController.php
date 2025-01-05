<?php

namespace App\Controller;

use App\DTO\ClientPayload;
use App\Entity\Client;
use App\Message\AddClientMessage;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
			description: 'Données du client à ajouter.',
			required: true,
			content: new OA\JsonContent(ref: new Model(type: Client::class))
		),
		tags: ['Clients'],
		responses: [
			new OA\Response(
				response: 201,
				description: 'Client ajouté avec succès.',
				content: new OA\JsonContent(
					properties: [
						new OA\Property(property: 'id', type: 'string', format: 'uuid')
					],
					type: 'object'
				)
			),
			new OA\Response(
				response: 400,
				description: 'Erreur de validation des données.',
				content: new OA\JsonContent(type: 'string')
			)
		]
	)]
	public function addClient(
		Request $request,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		MessageBusInterface $messageBus,
	): JsonResponse {
		try {
			$payload = $serializer->deserialize($request->getContent(), ClientPayload::class, 'json');

			$errors = $validator->validate($payload);

			if ($errors->count() > 0) {
				$errorMessages = [];
				foreach ($errors as $error) {
					$errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
				}
				return new JsonResponse(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
			}

			$messageBus->dispatch(new AddClientMessage($payload->name));

			return new JsonResponse(['message' => 'Client creation in progress'], Response::HTTP_ACCEPTED);
		} catch (\Exception $e) {
			return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
		}
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
