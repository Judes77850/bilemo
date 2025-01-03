<?php

namespace App\DTO;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class UserPayload
{
	#[Assert\NotBlank(message: "The firstname is required.")]
	public string $firstname;
	#[Assert\NotBlank(message: "The lastname is required.")]
	public string $lastname;
	#[Assert\NotBlank(message: "The clientId is required.")]
	public Uuid $clientId;
	#[Assert\NotBlank(message: "The email is required.")]
	public string $email;
	#[Assert\NotBlank(message: "The password is required.")]
	public string $password;

}
