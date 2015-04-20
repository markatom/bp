<?php

namespace Model\Entity;

use Kdyby\Doctrine\Entities\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use LogicException;

/**
 * Representation of order's states.
 *
 * @ORM\Embeddable
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class OrderState extends BaseEntity
{

	const PROCESSING = 'processing';
	const WAITING    = 'waiting';
	const POSTPONED  = 'postponed';
	const CANCELLED  = 'cancelled';
	const COMPLETED  = 'completed';

	/**
	 * Available states.
	 * @var array
	 */
	public static $transitions = [
		self::PROCESSING => [self::WAITING, self::POSTPONED, self::CANCELLED, self::COMPLETED],
		self::WAITING    => [self::PROCESSING],
		self::POSTPONED  => [self::PROCESSING],
		self::CANCELLED  => [],
		self::COMPLETED  => [],
	];

	/**
	 * Human readable names for states.
	 * @var array
	 */
	private static $names = [
		self::PROCESSING => 'Zpracovává se',
		self::WAITING    => 'Čeká se na klienta',
		self::POSTPONED  => 'Zatím odloženo',
		self::CANCELLED  => 'Zrušeno',
		self::COMPLETED  => 'Úspěšně ukončeno',
	];

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
    private $slug = self::PROCESSING;

	/**
	 * @return string
	 */
	public function getSlug()
	{
		return $this->slug;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return self::$names[$this->slug];
	}

	/**
	 * Tells if transition to given state is possible.
	 * @param string $state
	 * @return bool
	 */
	public function isTransitionPossible($state)
	{
		return in_array($state, self::$transitions[$this->slug]);
	}

	/**
	 * Performs a transition to given state if it is possible.
	 * @param string $state
	 */
	public function transition($state)
	{
		if (!$this->isTransitionPossible($state)) {
			throw new LogicException("Cannot transition from '$this->slug' to '$state'.");
		}

		$this->slug = $state;
	}

	/**
	 * Sets the requested state even if a transition is not possible.
	 * @param string $state
	 */
	public function forceTransition($state)
	{
		$this->slug = $state;
	}

}
