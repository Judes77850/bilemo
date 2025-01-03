<?php

namespace App\MessageHandler;

use App\Entity\Client;
use App\Entity\User;
use App\Message\AddUserMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AddUserHandler
{
	private EntityManagerInterface $entityManager;

	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	public function __invoke(AddUserMessage $message) :User
	{
		$client = $this->entityManager->getRepository(Client::class)->find($message->clientId);
		if (!$client) {
			throw new \InvalidArgumentException('Client not found');
		}

		$user = new User();
		$user->setFirstname($message->firstName);
		$user->setLastname($message->lastName);
		$user->setPassword($message->password);
		$user->setClient($client);
		$user->setEmail($message->email);

		$this->entityManager->persist($user);
		$this->entityManager->flush();

		return $user;
	}
}
