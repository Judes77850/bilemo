<?php

namespace App\Message;

use Symfony\Component\Uid\Uuid;

readonly class AddUserMessage
{

	public function __construct(
		public string $firstName,
		public string $lastName,
		public string $email,
		public Uuid   $clientId,
		public string $password)
	{

	}
}
