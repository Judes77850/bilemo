<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
	#[ORM\Id]
	#[ORM\GeneratedValue(strategy: 'NONE')]
	#[ORM\Column(type: 'uuid')]
	private Uuid $id;


	#[ORM\Column(type: 'string', length: 255, unique: true, nullable: false)]
	private string $email;

	#[ORM\Column(type: 'string')]
	private string $password;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private ?string $firstname = null;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private ?string $lastname = null;

	#[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'users')]
	#[ORM\JoinColumn(nullable: false)]
	private ?Client $client = null;


	public function __construct()
	{
		$this->id = Uuid::v7();
	}

	public function getId(): Uuid|UuidV7
	{
		return $this->id;
	}

	public function getUsername(): ?string
	{
		return $this->email;
	}

	public function getEmail(): ?string
	{
		return $this->email;
	}

	public function setEmail(string $email): self
	{
		$this->email = $email;

		return $this;
	}

	public function getPassword(): string
	{
		return $this->password;
	}

	public function setPassword(string $password): self
	{
		$this->password = $password;
		return $this;
	}

	public function getFirstname(): ?string
	{
		return $this->firstname;
	}

	public function setFirstname(?string $firstname): self
	{
		$this->firstname = $firstname;
		return $this;
	}

	public function getLastname(): ?string
	{
		return $this->lastname;
	}

	public function setLastname(?string $lastname): self
	{
		$this->lastname = $lastname;
		return $this;
	}

	public function getClient(): ?Client
	{
		return $this->client;
	}

	public function setClient(?Client $client): self
	{
		$this->client = $client;

		return $this;
	}

	public function getRoles(): array
	{
		return ['ROLE_USER'];
	}

	public function eraseCredentials(): void
	{
		// TODO: Implement eraseCredentials() method.
	}

	public function getUserIdentifier(): string
	{
		return $this->email;
	}
}
