<?php

namespace Model\Sql;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Model\Entity\Session;

/**
 * Filters expired sessions out.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class ExpiredSessionFilter extends SQLFilter
{

	/**
	 * Gets the SQL query part to add to a query.
	 * @param ClassMetaData $targetEntity
	 * @param string $targetTableAlias
	 * @return string The constraint SQL if there is available, empty string otherwise.
	 */
	public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
	{
		if ($targetEntity->reflClass->getName() !== Session::class) {
			return '';
		}

		return $targetTableAlias . '.expiration > NOW()';
	}

}
