<?php

namespace App\MessageHandler;

use App\Entity\Client;
use App\Message\AddClientMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AddClientHandler
{
	private EntityManagerInterface $entityManager;

	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	public function __invoke(AddClientMessage $message) :Client
	{
		$client = new Client();
		$client->setCompanyName($message->getName());

		$this->entityManager->persist($client);
		$this->entityManager->flush();

		return $client;
	}
}
