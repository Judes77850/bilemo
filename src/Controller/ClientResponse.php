<?php
namespace App\Controller;

use App\Entity\Client;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ClientResponse
{
	public string $id;
	public ?string $name = null;
	/**
	 * @var array<array<string, string>> Liste des utilisateurs, chaque élément est un tableau associatif.
	 */
	public array $users = [];

	/**
	 * @var array<string, string> Liste des liens, chaque élément est une clé associée à une URL.
	 */
	public array $links = [];

	public static function fromClient(Client $client, UrlGeneratorInterface $urlGenerator): self
	{
		$response = new self();
		$response->id = $client->getId();
		$response->name = $client->getCompanyName();

		foreach ($client->getUsers() as $user) {
			$response->users[] = [
				'userId' => (string) $user->getId(),
				'firstname' => $user->getFirstname() ?? '',
				'lastname' => $user->getLastname() ?? '',
				'email' => $user->getEmail() ?? '',
				'link' => $urlGenerator->generate('get_user', ['id' => $user->getId()]),
			];
		}

		$response->links = [
			'self' => $urlGenerator->generate('get_client', ['id' => $client->getId()]),
		];

		return $response;
	}
}
