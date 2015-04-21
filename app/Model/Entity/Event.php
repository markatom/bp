<?php

namespace Model\Entity;

use Exception;
use Kdyby\Doctrine\Entities\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use LogicException;
use Nette\Utils\DateTime;

/**
 * @ORM\Embeddable
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
abstract class Event extends BaseEntity
{

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
    protected $place;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 * @var DateTime
	 */
	protected $date;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @var string
	 */
	protected $description;

	/**
	 * @param string $place
	 * @param string $date
	 * @param string $description
	 */
	public function __construct($place, $date, $description)
	{
		$this->place       = $place;
		$this->description = $description;

		$this->setDate($date);
	}

	/**
	 * @param string $date
	 */
	public function setDate($date)
	{
		if ($date === NULL) {
			$this->date = NULL;
			return;
		}

		try {
			$date = DateTime::from($date);

		} catch (Exception $e) {
			throw new LogicException('Invalid date.');
		}

		$this->date = $date;
	}
	
}
