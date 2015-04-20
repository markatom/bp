<?php

namespace Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class Accident extends Event
{

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
    protected $causedBy;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $guilt;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $injury;

	/**
	 * @param string $place
	 * @param string $date
	 * @param string $description
	 * @param string $causedBy
	 * @param string $guilt
	 * @param string $injury
	 */
	public function __construct($place, $date, $description, $causedBy, $guilt, $injury)
	{
		$this->causedBy = $causedBy;
		$this->guilt    = $guilt;
		$this->injury   = $injury;

	    parent::__construct($place, $date, $description);
	}

}
