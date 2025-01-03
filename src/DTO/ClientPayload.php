<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ClientPayload
{
	#[Assert\NotBlank(message: "The name is required.")]
	public string $name;
}
