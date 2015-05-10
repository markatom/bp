<?php

namespace Presenter;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Model\Entity\Address;
use Model\Entity\Client;
use Nette\Http\IResponse;

/**
 * Clients resource controller.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class ClientsPresenter extends SecuredPresenter
{

	/**
	 * Creates a new client entity.
	 */
	public function actionCreate()
	{
		$email     = $this->getPost('email', NULL);
		$telephone = $this->getPost('telephone', NULL);

		if ($email === NULL && $telephone === NULL) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'emailOrTelephoneRequired');
		}

		$client = new Client(
			$this->getPost('fullName'),
			$this->getPost('dateOfBirth', NULL),
			$email,
			$telephone,
			new Address(
				$this->getPost(['address', 'street'], NULL),
				$this->getPost(['address', 'city'], NULL),
				$this->getPost(['address', 'zip'], NULL),
				$this->getPost(['address', 'country'], NULL)
			)
		);

		try {
			$this->em->persist($client)->flush();

		} catch (UniqueConstraintViolationException $e) {
			$this->sendError(IResponse::S409_CONFLICT, 'duplicateContact');
		}

		$this->sendJson(self::mapClient($client), IResponse::S201_CREATED);
	}

	/**
	 * Updates single client entity identified by id.
	 * @param int $id
	 */
	public function actionUpdate($id)
	{
		$client = $this->em->getRepository(Client::class)->find($id);

		if (!$client) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownClient');
		}

		$email     = $this->getPost('email', NULL);
		$telephone = $this->getPost('telephone', NULL);

		if ($email === NULL && $telephone === NULL) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'emailOrTelephoneRequired');
		}

		$client->fullName    = $this->getPost('fullName');
		$client->dateOfBirth = $this->getPost('dateOfBirth', NULL);
		$client->email       = $email;
		$client->telephone   = $telephone;
		$client->address     = new Address(
			$this->getPost(['address', 'street'], NULL),
			$this->getPost(['address', 'city'], NULL),
			$this->getPost(['address', 'zip'], NULL),
			$this->getPost(['address', 'country'], NULL)
		);

		try {
			$this->em->flush();

		} catch (UniqueConstraintViolationException $e) {
			$this->sendError(IResponse::S409_CONFLICT, 'duplicateContact');
		}

		$this->sendJson(self::mapClient($client));
	}

	/**
	 * Reads a single client entity identified by id.
	 * @param string $id
	 */
	public function actionRead($id)
	{
		$client = $this->em->getRepository(Client::class)->find($id);

		if (!$client) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownClient');
		}

		$this->sendJson(self::mapClient($client));
	}

	/**
	 * Reads all users with optional sorting and filters.
	 * @return array
	 */
	public function actionReadAll()
	{
		$qb = $this->em->getRepository(Client::class)->createQueryBuilder('c');

		if ($sort = $this->getQuery('sort', NULL)) {
			if (substr($sort, 0, 1) === '-') {
				$qb->orderBy('c.' . substr($sort, 1), 'DESC');
			} else {
				$qb->orderBy("c.$sort");
			}
		}

		foreach ($this->getQuery('filters', []) as $prop => $value) {
			if ($value === '') {
				continue;
			}
			$qb->andWhere("c.$prop LIKE :$prop")
				->setParameter($prop, "%$value%");
		}

		$clients = $qb->getQuery()->getResult();

		$this->sendJson(array_map([self::class, 'mapClient'], $clients));
    }

	/**
	 * Maps given client entity to an array.
	 * @param Client $client
	 * @return array
	 */
	public static function mapClient(Client $client)
	{
		return [
			'id'          => $client->id,
			'fullName'    => $client->fullName,
			'dateOfBirth' => $client->dateOfBirth ? $client->dateOfBirth->format(self::DATE_FORMAT) : NULL,
			'email'       => $client->email,
			'telephone'   => $client->telephone,
			'address'     => [
				'street'  => $client->address->street,
				'city'    => $client->address->city,
				'zip'     => $client->address->zip,
				'country' => $client->address->country,
			],
		];
	}

}
