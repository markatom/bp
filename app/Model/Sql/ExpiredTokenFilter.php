<?php

namespace Model\Sql;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Model\Entity\Token;

/**
 * Filters expired tokens out.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class ExpiredTokenFilter extends SQLFilter
{

	/**
	 * Gets the SQL query part to add to a query.
	 * @param ClassMetaData $targetEntity
	 * @param string $targetTableAlias
	 * @return string The constraint SQL if there is available, empty string otherwise.
	 */
	public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
	{
		if ($targetEntity->reflClass->getName() !== Token::class) {
			return '';
		}

		return $targetTableAlias . '.expiration > NOW()';
	}

}