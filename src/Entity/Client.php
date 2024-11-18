<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
	#[ORM\Id]
	#[ORM\GeneratedValue(strategy: 'NONE')]
	#[ORM\Column(type: 'uuid')]
	private Uuid $id;

	#[ORM\Column(type: 'string', length: 255)]
	private ?string $companyName = null;

	#[ORM\OneToMany(targetEntity: User::class, mappedBy: 'client', cascade: ['persist', 'remove'])]
	private Collection $users;

	public function __construct()
	{
		$this->id = Uuid::v7();
		$this->users = new ArrayCollection();
	}

	public function getId(): Uuid|UuidV7
	{
		return $this->id;
	}

	public function getCompanyName(): ?string
	{
		return $this->companyName;
	}

	public function setCompanyName(string $companyName): self
	{
		$this->companyName = $companyName;

		return $this;
	}

	/**
	 * @return Collection<int, User>
	 */
	public function getUsers(): Collection
	{
		return $this->users;
	}

	public function addUser(User $user): self
	{
		if (!$this->users->contains($user)) {
			$this->users[] = $user;
			$user->setClient($this);
		}

		return $this;
	}

	public function removeUser(User $user): self
	{
		if ($this->users->removeElement($user)) {
			if ($user->getClient() === $this) {
				$user->setClient(null);
			}
		}

		return $this;
	}
}
