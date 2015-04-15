<?php

namespace Presenter;

use Model\Entity\Role;

/**
 * Roles resource controller.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class RolesPresenter extends SecuredPresenter
{

	/**
	 * Reads all roles.
	 */
	public function actionReadAll()
	{
		$roles = $this->em->getRepository(Role::class)->findAll();

		$this->sendJson(array_map([self::class, 'mapRole'], $roles));
    }

	/**
	 * Maps role entity to an array.
	 * @param Role $role
	 * @return array
	 */
	public static function mapRole(Role $role)
	{
		return [
			'id'   => $role->id,
			'slug' => $role->slug,
			'name' => $role->name,
		];
	}

}
