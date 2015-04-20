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
	public static $states = [
		self::PROCESSING,
		self::WAITING,
		self::POSTPONED,
		self::CANCELLED,
		self::COMPLETED,
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
	 * Performs a transition to the processing state if it is possible.
	 */
	public function transitionProcessing()
	{
		if ($this->slug !== self::POSTPONED || $this->slug !== self::WAITING) {
			throw new LogicException("Cannot transition from '$this->slug' to 'processing'.");
		}

		$this->slug = self::PROCESSING;
	}

	/**
	 * Performs a transition to the processing state if it is possible.
	 */
	public function transitionWaiting()
	{
		if ($this->slug !== self::PROCESSING) {
			throw new LogicException("Cannot transition from '$this->slug' to 'waiting'.");
		}
		
		$this->slug = self::WAITING;
	}

	/**
	 * Performs a transition to the processing state if it is possible.
	 */
	public function transitionPostponed()
	{
		if ($this->slug !== self::PROCESSING) {
			throw new LogicException("Cannot transition from '$this->slug' to 'postponed'.");
		}

		$this->slug = self::POSTPONED;
	}

	/**
	 * Performs a transition to the processing state if it is possible.
	 */
	public function transitionCancelled()
	{
		if ($this->slug !== self::PROCESSING) {
			throw new LogicException("Cannot transition from '$this->slug' to 'cancelled'.");
		}

		$this->slug = self::CANCELLED;
	}

	/**
	 * Performs a transition to the processing state if it is possible.
	 */
	public function transitionCompleted()
	{
		if ($this->slug !== self::PROCESSING) {
			throw new LogicException("Cannot transition from '$this->slug' to 'completed'.");
		}

		$this->slug = self::COMPLETED;
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
