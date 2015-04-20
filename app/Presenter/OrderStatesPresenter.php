<?php

namespace Presenter;

use Model\Entity\OrderState;
use Nette\Object;

/**
 * Order's states resource controller.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class OrderStatesPresenter extends SecuredPresenter
{

	/**
	 * Reads all available states.
	 */
	public function actionReadAll()
	{
		$states = self::createFromSlugs(array_keys(OrderState::$transitions));

		$this->sendJson(array_map([self::class, 'mapOrderState'], $states));
    }

	/**
	 * Maps given order's state embeddable to an array.
	 * @param OrderState $orderState
	 * @return array
	 */
	public static function mapOrderState(OrderState $orderState)
	{
		return [
			'name' => $orderState->name,
			'slug' => $orderState->slug,
			'next' => array_map(function (OrderState $state) {
				return [
					'name' => $state->name,
					'slug' => $state->slug,
				];
			}, self::createFromSlugs(OrderState::$transitions[$orderState->slug])),
		];
	}

	/**
	 * @param array $slugs
	 * @return OrderState[]
	 */
	private static function createFromSlugs(array $slugs)
	{
		return array_map(function ($state) {
			$orderState = new OrderState;
			$orderState->forceTransition($state);
			return $orderState;
		}, $slugs);
	}

}
