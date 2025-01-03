<?php

namespace App\Controller;

class PaginatedResponse
{
	/**
	 * @var array<ClientResponse|ProductResponse|UserResponse> $items
	 */
	public array $items;
	public int $currentPage;
	public int $totalPages;
	public int $totalItems;
	public int $itemsPerPage;

	/**
	 * @param array<ClientResponse|ProductResponse|UserResponse> $items
	 */
	public function __construct(
		array $items, int $currentPage, int $totalPages, int $totalItems, int $itemsPerPage)
	{
		$this->items = $items;
		$this->currentPage = $currentPage;
		$this->totalPages = $totalPages;
		$this->totalItems = $totalItems;
		$this->itemsPerPage = $itemsPerPage;
	}

	/**
	 * @return array{pagination: array{currentPage: int, totalPages: int, totalItems: int, itemsPerPage: int}, data: array<ClientResponse|ProductResponse|UserResponse>}
	 */
	public function toArray(): array
	{
		return [
			'pagination' => [
				'currentPage' => $this->currentPage,
				'totalPages' => $this->totalPages,
				'totalItems' => $this->totalItems,
				'itemsPerPage' => $this->itemsPerPage,
			],
			'data' => $this->items,
		];
	}
}

