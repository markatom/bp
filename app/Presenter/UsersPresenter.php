<?php

namespace Presenter;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Email\LostPassword\LostPasswordSender;
use Email\UserCreated\UserCreatedSender;
use Model\Entity\Role;
use Model\Entity\User;
use Model\Service\Tokens;
use Nette\Http\IResponse;
use Nette\Utils\Strings;

/**
 * Users resource controller.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class UsersPresenter extends SecuredPresenter
{

	/** @var UserCreatedSender @inject */
	public $userCreatedSender;

	/** @var LostPasswordSender @inject */
	public $lostPasswordSender;

	/** @var Tokens @inject */
	public $tokens;

	/**
	 * Lost password will not authenticate.
	 */
	public function startup()
	{
		if (in_array($this->action, ['updateUserByToken', 'requestPasswordChange'])) {
			ApiPresenter::startup(); // no authorization

		} else {
			parent::startup(); // authorization required
		}
	}

	/**
	 * Reads all users with optional filters.
	 */
	public function actionReadAll()
	{
		$qb = $this->em->getRepository(User::class)->createQueryBuilder('u');
		$qb->andWhere('u.deleted = false');

		foreach ($this->getQuery('filters', []) as $prop => $value) {
			if ($value === '') {
				continue;
			}
			$qb->andWhere("u.$prop LIKE :$prop")
				->setParameter($prop, "%$value%");
		}

		$qb->addOrderBy('u.fullName');
		$users = $qb->getQuery()->getResult();

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

		try {
			$this->em->persist($user = new User($this->getPost('fullName'), $this->getPost('email'), $role));
			$token = $this->tokens->create($user, 'setPassword', '+24 hours');

		} catch (UniqueConstraintViolationException $e) {
			$this->sendError(IResponse::S409_CONFLICT, 'emailConflict');
		}

		$this->userCreatedSender->send($token->key, $user->email);

		$this->em->flush();

		$this->sendJson(self::mapUser($user), IResponse::S201_CREATED);
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
		$roleId = $this->getPost(['role', 'id'], NULL);

		if ($this->user->role->slug !== 'admin' && ($this->user->id != $id || $roleId)) { // weak comparison intentionally
			$this->sendError(IResponse::S403_FORBIDDEN, 'adminOnly');
		}

		$user = $this->em->getRepository(User::class)->find($id);

		if (!$user) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownUser');
		}

		$user->fullName = $this->getPost('fullName');

		if ($roleId) {
			$role = $this->em->getRepository(Role::class)->find($roleId);

			if (!$role) {
				$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownRole');
			}

			$user->role = $role;
		}

		$this->em->flush();

		$this->sendJson(self::mapUser($user));
	}

	/**
	 * Requests password change for user identified by given email.
	 */
	public function actionRequestPasswordChange()
	{
		$user = $this->em->getRepository(User::class)->findOneBy(['email' => $this->getQuery('email')]);

		if (!$user) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownUser');
		}

		$token = $this->tokens->create($user, 'setPassword', '+24 hours');

		$this->lostPasswordSender->send($token->key, $user->email);

		$this->sendJson([
			'token' => [
				'key' => $token->key,
			],
		]);
	}

	/**
	 * Updates password of user identified by provided token key.
	 */
	public function actionUpdateUserByToken()
	{
		if (!$token = $this->tokens->get($this->getQuery(['token', 'key']), 'setPassword')) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'invalidToken', 'Invalid token key. Token maybe expired.');
		}

		$password = $this->getPost('password');

		if (Strings::length($password) < 5) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'shortPassword');
		}

		$token->user->setPassword($password);
		$this->tokens->delete($token);

		$this->em->flush();

		$this->sendJson($this->mapUser($token->user));
	}

	/**
	 * Deletes single user entity.
	 * @param int $id
	 */
	public function actionDelete($id)
	{
		/** @var User $user */
		$user = $this->em->getRepository(User::class)->find($id);

		if (!$user) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownUser');
		}

		if ($this->user->id === $user->id) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'cannotDeleteYourself');
		}

		$user->delete();
		$this->em->flush();
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
