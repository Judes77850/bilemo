<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
	private EntityManagerInterface $entityManager;

	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	#[Route('/api/users', name: 'get_users', methods: ['GET'])]
	public function getUsers(): JsonResponse
	{
		$users = $this->entityManager->getRepository(User::class)->findAll();
		$data = [];

		foreach ($users as $user) {
			$data[] = [
				'id' => $user->getUserById(),
				'username' => $user->getUsername(),
			];
		}

		return new JsonResponse($data);
	}


	#[Route('/api/user/{id}', name: 'get_user', methods: ['GET'])]
	public function getUserById(int $id): JsonResponse
	{
		$user = $this->entityManager->getRepository(User::class)->find($id);

		if (!$user) {
			return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
		}

		$data = [
			'id' => $user->getId(),
			'username' => $user->getUsername(),
		];

		return new JsonResponse($data);
	}


	#[Route('/api/user', name: 'add_user', methods: ['POST'])]
	public function addUser(Request $request): JsonResponse
	{
		$data = json_decode($request->getContent(), true);

		if (!isset($data['username']) || !isset($data['client_id'])) {
			return new JsonResponse(['error' => 'Missing "username" or "client_id"'], Response::HTTP_BAD_REQUEST);
		}

		$client = $this->entityManager->getRepository(Client::class)->find($data['client_id']);

		if (!$client) {
			return new JsonResponse(['error' => 'Client not found'], Response::HTTP_BAD_REQUEST);
		}

		$user = new User();
		$user->setUsername($data['username']);
		$user->setEmail($data['email']);
		$user->setClient($client);

		$this->entityManager->persist($user);
		$this->entityManager->flush();

		return new JsonResponse(['id' => $user->getId()], Response::HTTP_CREATED);
	}


	#[Route('/api/user/{id}', name: 'delete_user', methods: ['DELETE'])]
	public function deleteUser(int $id): JsonResponse
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
