<?php

namespace App\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends AbstractController
{
	private EntityManagerInterface $entityManager;

	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}


	#[Route('/api/clients', name: 'get_clients', methods: ['GET'])]
	public function getClients(): JsonResponse
	{
		$clients = $this->entityManager->getRepository(Client::class)->findAll();
		$data = [];

		foreach ($clients as $client) {
			$data[] = [
				'id' => $client->getId(),
				'name' => $client->getCompanyName(),
			];
		}

		return new JsonResponse($data);
	}

	#[Route('/api/client/{id}', name: 'get_client', methods: ['GET'])]
	public function getClient(int $id): JsonResponse
	{
		$client = $this->entityManager->getRepository(Client::class)->find($id);

		if (!$client) {
			return new JsonResponse(['error' => 'Client not found'], Response::HTTP_NOT_FOUND);
		}

		$data = [
			'id' => $client->getId(),
			'name' => $client->getCompanyName(),
		];

		return new JsonResponse($data);
	}

	#[Route('/api/client', name: 'add_client', methods: ['POST'])]
	public function addClient(Request $request): JsonResponse
	{

		try {
			$data = json_decode($request->getContent(), true);

			if (!isset($data['name'])) {
				return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
			}

			$client = new Client();
			$client->setCompanyName($data['name']);


			$this->entityManager->persist($client);
			$this->entityManager->flush();

			return new JsonResponse(['id' => $client->getId()], Response::HTTP_CREATED);
		} catch (\Exception $e) {
			return $this->json(['error' => 'Une erreur est survenue : ' . $e->getMessage()], 500);
		}

	}

	#[Route('/api/client/{id}', name: 'delete_client', methods: ['DELETE'])]
	public function deleteClient(int $id): JsonResponse
	{
		$client = $this->entityManager->getRepository(Client::class)->find($id);

		if (!$client) {
			return new JsonResponse(['error' => 'client not found'], Response::HTTP_NOT_FOUND);
		}

		$this->entityManager->remove($client);
		$this->entityManager->flush();

		return new JsonResponse(null, Response::HTTP_NO_CONTENT);
	}
}
