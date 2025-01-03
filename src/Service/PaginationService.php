<?php

namespace App\Service;

use App\Controller\ClientResponse;
use App\Controller\ProductResponse;
use App\Controller\UserResponse;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\Query;
use App\Controller\PaginatedResponse;

class PaginationService
{
	private PaginatorInterface $paginator;

	public function __construct(PaginatorInterface $paginator)
	{
		$this->paginator = $paginator;
	}

	public function paginate(Query $query, int $page, int $itemsPerPage, callable $transformer): PaginatedResponse
	{

		$pagination = $this->paginator->paginate($query, $page, $itemsPerPage);


		/** @var array<ClientResponse|ProductResponse|UserResponse> $items */

		$items = array_map($transformer, [...$pagination->getItems()]);

		return new PaginatedResponse(
			$items,
			$page,
			/** @phpstan-ignore-next-line */
			$pagination->getPaginationData()['pageCount'],
			$pagination->getTotalItemCount(),
			$itemsPerPage
		);
	}
}
