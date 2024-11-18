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
use Symfony\Component\Uid\Uuid;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
	private EntityManagerInterface $entityManager;
	private UserPasswordHasherInterface $passwordHasher;

	public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
	{
		$this->entityManager = $entityManager;
		$this->passwordHasher = $passwordHasher;
	}

	#[Route('/api/users', name: 'get_users', methods: ['GET'])]
	public function getUsers(): JsonResponse
	{
		$users = $this->entityManager->getRepository(User::class)->findAll();
		$data = [];

		foreach ($users as $user) {
			$data[] = [
				'id' => $user->getId(),
				'username' => $user->getUsername(),
				'firstname' => $user->getFirstname(),
				'lastname' => $user->getLastname(),
				'clientId' => $user->getClient()->getId(),
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

		if (!isset($data['username'], $data['email'], $data['password'], $data['client'])) {
			return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
		}

		$formattedUuid = preg_replace(
			'/^(.{8})(.{4})(.{4})(.{4})(.{12})$/',
			'$1-$2-$3-$4-$5',
			strtoupper($data['client'])
		);

		try {
			$clientUuid = Uuid::fromString($formattedUuid);
		} catch (\InvalidArgumentException $e) {
			return new JsonResponse(['error' => 'Invalid client UUID format'], Response::HTTP_BAD_REQUEST);
		}

		$client = $this->entityManager->getRepository(Client::class)->find($clientUuid);

		if (!$client) {
			return new JsonResponse(['error' => 'Client not found'], Response::HTTP_BAD_REQUEST);
		}

		$user = new User();
		$user->setEmail($data['email']);
		$user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
		$user->setClient($client);

		$this->entityManager->persist($user);
		$this->entityManager->flush();

		return new JsonResponse(['id' => $user->getId()->toRfc4122()], Response::HTTP_CREATED);
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
