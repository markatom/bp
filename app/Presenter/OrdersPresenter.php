<?php

namespace Presenter;

use Kdyby\Doctrine\QueryBuilder;
use Kdyby\GeneratedProxy\__CG__\Model\Entity\Client;
use Model\Entity\Order;
use Model\Entity\Event;
use Model\Entity\Accident;
use LogicException;
use Model\Entity\OrderState;
use Model\Entity\User;
use Nette\Http\IResponse;
use Nette\Utils\Random;

/**
 * Orders resource controller.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class OrdersPresenter extends SecuredPresenter
{

	/**
	 * Creates a new order entity.
	 */
	public function actionCreate()
	{
		$client = $this->em->getRepository(Client::class)->find($this->getPost(['client', 'id']));
		if (!$client) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownClient');
		}

		if ($assigneeId = $this->getPost(['assignee', 'id'], NULL)) {
			$assignee = $this->em->getRepository(User::class)->find($assigneeId);
		}

		$order = new Order(
			$this->getPost('name'),
			new Accident(
				$this->getPost(['event', 'place'], NULL),
				$this->getPost(['event', 'date'], NULL),
				$this->getPost(['event', 'description'], NULL),
				$this->getPost(['event', 'causedBy'], NULL),
				$this->getPost(['event', 'quilt'], NULL),
				$this->getPost(['event', 'injury'], NULL)
			),
			$this->user,
			$client,
			isset($assignee) ? $assignee : NULL
		);

		$this->em->persist($order)->flush();

		$this->sendJson(self::mapOrder($order), IResponse::S201_CREATED);
	}

	/**
	 * Reads all orders with optional sorting and filters.
	 */
	public function actionReadAll()
	{
		$qb = $this->em->getRepository(Order::class)->createQueryBuilder('o');

		if ($sort = $this->getQuery('sort', NULL)) {
			if (substr($sort, 0, 1) === '-') {
				$sort = substr($sort, 1);
				$dir = 'DESC';
			} else {
				$dir = 'ASC';
			}
			$qb->orderBy($this->joinRelated($qb, $sort), $dir);
		}

		$i = 0;
		foreach ($this->getQuery('filters', []) as $prop => $value) {
			if ($value === '') {
				continue;
			}
			$qb->where($this->joinRelated($qb, $prop) . " LIKE ?$i")
				->setParameter($i, "%$value%");
			$i++;
		}

		$orders = $qb->getQuery()->getResult();

		$this->sendJson(array_map([self::class, 'mapOrder'], $orders));
    }

	/**
	 * Reads a single order entity.
	 * @param int $id
	 */
	public function actionRead($id)
	{
		$order = $this->em->getRepository(Order::class)->find($id);

		if (!$order) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownOrder');
		}

		$this->sendJson(self::mapOrder($order));
	}

	/**
	 * Updates a single order entity.
	 * @param int $id
	 */
	public function actionUpdate($id)
	{
		$order = $this->em->getRepository(Order::class)->find($id);
		if (!$order) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownOrder');
		}

		$client = $this->em->getRepository(Client::class)->find($this->getPost(['client', 'id']));
		if (!$client) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownClient');
		}

		if ($assigneeId = $this->getPost(['assignee', 'id'], NULL)) {
			$assignee = $this->em->getRepository(User::class)->find($assigneeId);
		}

		$order->name     = $this->getPost('name');
		$order->client   = $client;
		$order->assignee = isset($assignee) ? $assignee : NULL;

		$order->event->place       = $this->getPost(['event', 'place'], NULL);
		$order->event->date        = $this->getPost(['event', 'date'], NULL);
		$order->event->description = $this->getPost(['event', 'description'], NULL);
		$order->event->causedBy    = $this->getPost(['event', 'causedBy'], NULL);
		$order->event->guilt       = $this->getPost(['event', 'guilt'], NULL);
		$order->event->injury      = $this->getPost(['event', 'injury'], NULL);

		$slug = $this->getPost(['state', 'slug']);
		if ($order->state->slug !== $slug) {
			$order->state->transition($slug);
		}

		$this->em->flush();

		$this->sendJson(self::mapOrder($order));
	}

	/**
	 * Maps given order entity to an array.
	 * @param Order $order
	 * @return array
	 */
	public static function mapOrder(Order $order)
	{
		return [
			'id'        => $order->id,
			'name'      => $order->name,
			'state'     => OrderStatesPresenter::mapOrderState($order->state),
			'event'     => self::mapEvent($order->event),
			'createdAt' => $order->createdAt->format(self::DATE_TIME_FORMAT),
			'createdBy' => UsersPresenter::mapUser($order->createdBy),
			'assignee'  => $order->assignee ? UsersPresenter::mapUser($order->assignee) : NULL,
			'client'    => ClientsPresenter::mapClient($order->client),
		];
	}

	/**
	 * Maps given event embeddable to an array.
	 * @param Event $event
	 * @return array
	 */
	public static function mapEvent(Event $event)
	{
		$mapped = [
			'place'       => $event->place,
			'date'        => $event->date ? $event->date->format(self::DATE_FORMAT) : NULL,
			'description' => $event->description
		];

		if ($event instanceof Accident) {
			return array_merge($mapped, self::mapAccident($event));

		} else {
			throw new LogicException('Cannot map an unknown subclass of the Event entity.');
		}
	}

	/**
	 * @param QueryBuilder $qb
	 * @param string $properties
	 * @return string
	 */
	private function joinRelated(QueryBuilder $qb, $properties)
	{
		$properties    = explode('.', $properties);
		$last          = array_pop($properties);
		$previousAlias = $qb->getRootAliases()[0];

		$em   = $qb->getEntityManager();
		$meta = $em->getClassMetadata($qb->getRootEntities()[0]);

		foreach ($properties as $property) {
			if (isset($meta->associationMappings[$property])) {
				$meta  = $em->getClassMetadata($meta->associationMappings[$property]['targetEntity']);
				$alias = $property . '_' . Random::generate(5);

				$qb->leftJoin($previousAlias . '.' . $property, $alias);

				$previousAlias = $alias;

			} else {
				$previousAlias .= '.' . $property;
			}
		}

		return $previousAlias . '.' . $last;
	}

	/**
	 * Maps given accident embeddable to an array.
	 * @param Accident $accident
	 * @return array
	 */
	private static function mapAccident(Accident $accident)
	{
		return [
			'causedBy' => $accident->causedBy,
			'guilt'    => $accident->guilt,
			'injury'   => $accident->injury,
		];
	}

}
