<?php

namespace App\Controller;

use AllowDynamicProperties;
use App\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

#[AllowDynamicProperties]
class UserResponse
{
	public string|null $firstName;
	public string|null $lastName;
	public Uuid $id;
	public string|null $email;
	public string|null $client;
	/** @var array<string, string> */
	public array $links;

	public static function fromUser(User $user, UrlGeneratorInterface $urlGenerator): self
	{
		$response = new self();
		$response->firstName = $user->getFirstName()?? null;
		$response->lastName = $user->getLastname()?? null;
		$response->email = $user->getEmail()?? null;
		$client = $user->getClient();
		$response->client = $client?->getCompanyName();
		$response->id = $user->getId();
		$response->links = [
			'self' => $urlGenerator->generate('get_user', ['id' => $user->getId()]),
		];

		return $response;
	}
}
