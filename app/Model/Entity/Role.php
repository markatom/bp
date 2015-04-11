<?php

namespace Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class Role extends BaseEntity
{

    use Identifier;

	/**
	 * @ORM\Column(type="string", unique=true)
	 * @var string
	 */
	protected $slug;

	/**
	 * @ORM\Column(type="string", unique=true)
	 * @var string
	 */
	protected $name;

	/**
	 * @param string $slug
	 * @param string $name
	 */
	public function __construct($slug, $name)
	{
	    $this->slug = $slug;
		$this->name = $name;
	}

}
