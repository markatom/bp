<?php

namespace Presenter;

use Kdyby\Doctrine\Tools\NonLockingUniqueInserter;
use Model\Entity\Role;
use Model\Entity\User;
use Nette\Http\IResponse;

/**
 * @todo Fill desc.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class UsersPresenter extends SecuredPresenter
{

	/**
	 * Reads all users.
	 */
	public function actionReadAll()
	{
		$users = $this->em->getRepository(User::class)->findAll();

		$this->sendJson(array_map([self::class, 'mapUser'], $users));
	}

	/**
	 * Creates a new entity.
	 */
	public function actionCreate()
	{
		$role = $this->em->getRepository(Role::class)->find($this->getPost(['role', 'id']));

		if (!$role) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownRole');
		}

		$user = new User($this->getPost('fullName'), $this->getPost('email'), $role);

		$inserter = new NonLockingUniqueInserter($this->em);

		$user = $inserter->persist($user);

		if (!$user) {
			$this->sendError(IResponse::S409_CONFLICT, 'emailConflict');
		}

		$this->sendJson(self::mapUser($user));
	}

	/**
	 * Reads single user entity.
	 * @param int $id
	 */
	public function actionRead($id)
	{
		$user = $this->em->getRepository(User::class)->find($id);

		if (!$user) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownUser');
		}

		$this->sendJson(self::mapUser($user));
	}

	/**
	 * Updates single user entity.
	 * @param int $id
	 */
	public function actionUpdate($id)
	{
		$user = $this->em->getRepository(User::class)->find($id);

		if (!$user) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownUser');
		}

		$role = $this->em->getRepository(Role::class)->find($this->getPost(['role', 'id']));

		if (!$role) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownRole');
		}

		$user->fullName = $this->getPost('fullName');
		$user->role     = $role;

		$this->em->flush();

		$this->sendEmpty();
	}

	/**
	 * Deletes single user entity.
	 * @param int $id
	 */
	public function actionDelete($id)
	{
		sleep(2);
		$user = $this->em->getRepository(User::class)->find($id);

		if (!$user) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownUser');
		}

		if ($this->user->id === $user->id) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'cannotDeleteYourself');
		}

		$this->em->remove($user)->flush();

		$this->sendEmpty();
	}

	/**
	 * Maps user entity to an array.
	 * @param User $user
	 * @return array
	 */
	public static function mapUser(User $user)
	{
		return [
			'id'       => $user->id,
			'fullName' => $user->fullName,
			'email'    => $user->email,
			'role'     => RolesPresenter::mapRole($user->role),
		];
	}



}
