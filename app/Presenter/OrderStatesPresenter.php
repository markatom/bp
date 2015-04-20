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
		$states = array_map(function ($state) {
			$orderState = new OrderState;
			$orderState->forceTransition($state);
			return $orderState;
		}, OrderState::$states);

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
		];
	}

}
