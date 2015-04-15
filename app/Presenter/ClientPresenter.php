<?php

namespace Presenter;

use Model\Entity\Address;
use Model\Entity\Client;
use Nette\Http\IResponse;

/**
 * Clients resource controller.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class ClientPresenter extends SecuredPresenter
{

	/**
	 * Creates a new client entity.
	 */
	public function actionCreate()
	{
		$client = new Client(
			$this->getPost('fullName'),
			$this->getPost('dateOfBirth'),
			$this->getPost('telephone'),
			new Address(
				$this->getPost(['address', 'street']),
				$this->getPost(['address', 'city']),
				$this->getPost(['address', 'zip']),
				$this->getPost(['address', 'country'])
			)
		);

		$this->em->persist($client)->flush();

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

		$client->fullName    = $this->getPost('fullName');
		$client->dateOfBirth = $this->getPost('dateOfBirth');
		$client->telephone   = $this->getPost('telephone');
		$client->address     = new Address(
			$this->getPost(['address', 'street']),
			$this->getPost(['address', 'city']),
			$this->getPost(['address', 'zip']),
			$this->getPost(['address', 'country'])
		);

		$this->em->flush();

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
	 * Reads all users.
	 * @return array
	 */
	public function actionReadAll()
	{
		$clients = $this->em->getRepository(Client::class)->findAll();

		return array_map([self::class, 'mapClient'], $clients);
    }

	/**
	 * Maps given client entity to an array.
	 * @param Client $client
	 * @return array
	 */
	public static function mapClient(Client $client)
	{
		return [
			'fullName'    => $client->fullName,
			'dateOfBirth' => $client->dateOfBirth,
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
