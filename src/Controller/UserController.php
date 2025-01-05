<?php

namespace App\Controller;

use App\DTO\UserPayload;
use App\Entity\User;
use App\Message\AddUserMessage;
use App\Repository\ClientRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\HandleTrait;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class UserController extends AbstractController
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

	#[Route('/api/users', name: 'get_users', methods: ['GET'])]
	#[OA\Get(
		summary: 'Recupere une liste paginee de users.',
		tags: ['Users'],
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
				description: 'Liste paginee des users.',
				content: new OA\JsonContent(
					type: 'array',
					items: new OA\Items(ref: new Model(type: User::class, groups: ['user_list']))
				)
			),
		]
	)]
	public function getUsers(Request $request, UrlGeneratorInterface $urlGenerator): JsonResponse
	{
		$query = $this->entityManager->getRepository(User::class)->createQueryBuilder('u')->getQuery();
		$page = max((int)$request->query->get('page', 1), 1);
		$paginatedResponse = $this->paginationService->paginate(
			$query,
			$page,
			5,
			fn(User $user) => UserResponse::fromUser($user, $urlGenerator)
		);
		return new JsonResponse($paginatedResponse->toArray());

	}


	#[Route('/api/users/{id}', name: 'get_user', methods: ['GET'])]
	#[OA\Get(
		summary: 'Recupere les details d\'un user.',
		tags: ['Users'],
		parameters: [
			new OA\Parameter(
				name: 'id',
				description: 'ID du user a recuperer.',
				in: 'path',
				schema: new OA\Schema(type: 'string', format: 'uuid')
			),
		],
		responses: [
			new OA\Response(
				response: 200,
				description: 'Details du user.',
				content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user_detail']))
			),
			new OA\Response(
				response: 404,
				description: 'User non trouve.',
				content: new OA\JsonContent(properties: [
					new OA\Property(property: 'error', type: 'string')
				], type: 'object')
			)
		]
	)]
	public function getUserById(Uuid $id, UrlGeneratorInterface $urlGenerator): JsonResponse
	{
		$user = $this->entityManager->getRepository(User::class)->find($id);
		if (!$user) {
			return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
		}
		$response = UserResponse::fromUser($user, $urlGenerator);
		return new JsonResponse($response);
	}

	#[Route('/api/users', name: 'add_user', methods: ['POST'])]
	#[OA\Post(
		summary: 'Ajoute un user.',
		requestBody: new OA\RequestBody(
			description: 'Donnees du user a ajouter.',
			required: true,
			content: new OA\JsonContent(ref: new Model(type: UserPayload::class))
		),
		tags: ['Users'],
		responses: [
			new OA\Response(
				response: 201,
				description: 'User ajoute avec succes.',
				content: new OA\JsonContent(
					properties: [
						new OA\Property(property: 'id', type: 'string', format: 'uuid')
					],
					type: 'object'
				)
			),
		]
	)]
	public function addUser(
		Request $request,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		UserPasswordHasherInterface $passwordHasher,
		ClientRepository $clientRepository,
		MessageBusInterface $messageBus,
	): JsonResponse {
		try {
			$payload = $serializer->deserialize($request->getContent(), UserPayload::class, 'json');

			$errors = $validator->validate($payload);

			if ($errors->count() > 0) {
				$errorMessages = [];
				foreach ($errors as $error) {
					$errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
				}
				return new JsonResponse(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
			}

			$client = $clientRepository->find($payload->clientId);

			if (!$client) {
				return new JsonResponse(['error' => 'Client not found'], JsonResponse::HTTP_BAD_REQUEST);
			}

			$hashedPassword = $passwordHasher->hashPassword(new User(), $payload->password);

			$messageBus->dispatch(new AddUserMessage(
				$payload->firstname,
				$payload->lastname,
				$payload->email,
				$payload->clientId,
				$hashedPassword
			));

			return new JsonResponse(['message' => 'User creation in progress'], Response::HTTP_ACCEPTED);
		} catch (\Exception $e) {
			return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
		}
	}


	#[Route('/api/users/{id}', name: 'delete_user', methods: ['DELETE'])]
	#[OA\Delete(
		summary: 'Supprime un user.',
		tags: ['Users'],
		parameters: [
			new OA\Parameter(
				name: 'id',
				description: 'ID du user a supprimer.',
				in: 'path',
				schema: new OA\Schema(type: 'string', format: 'uuid')
			),
		],
		responses: [
			new OA\Response(
				response: 204,
				description: 'User supprime avec succes.'
			),
			new OA\Response(
				response: 404,
				description: 'User non trouve.',
				content: new OA\JsonContent(properties: [
					new OA\Property(property: 'error', type: 'string')
				], type: 'object')
			)
		]
	)]
	public function deleteUser(Uuid $id): JsonResponse
	{
		$user = $this->entityManager->getRepository(User::class)->find($id);
		if (!$user) {
			return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
		}
		$this->entityManager->remove($user);
		$this->entityManager->flush();

		return new JsonResponse(null, Response::HTTP_NO_CONTENT);
	}
}
