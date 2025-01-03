<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use InvalidArgumentException;

class MapRequestPayload
{
	private SerializerInterface $serializer;
	private ValidatorInterface $validator;

	public function __construct(SerializerInterface $serializer, ValidatorInterface $validator)
	{
		$this->serializer = $serializer;
		$this->validator = $validator;
	}

	public function map(Request $request, string $dtoClass): mixed
	{
		$dto = $this->serializer->deserialize(
			$request->getContent(),
			$dtoClass,
			'json'
		);

		$errors = $this->validator->validate($dto);

		if (count($errors) > 0) {
			throw new InvalidArgumentException((string) $errors);
		}

		return $dto;
	}
}