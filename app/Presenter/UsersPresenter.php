<?php

namespace Presenter;

use DateTime;
use Email\UserCreated\UserCreatedSender;
use Kdyby\Doctrine\Tools\NonLockingUniqueInserter;
use Model\Entity\Role;
use Model\Entity\Token;
use Model\Entity\User;
use Nette\Http\IResponse;

/**
 * Users resource controller.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class UsersPresenter extends SecuredPresenter
{

	/** @var UserCreatedSender @inject */
	public $userCreatedSender;

	public function startup()
	{
		if ($this->action === 'updateUserByToken') {
			ApiPresenter::startup(); // no authorization

		} else {
			parent::startup(); // authorization required
		}
	}

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

		$user     = new User($this->getPost('fullName'), $this->getPost('email'), $role);
		$inserter = new NonLockingUniqueInserter($this->em);
		$user     = $inserter->persist($user);

		if (!$user) {
			$this->sendError(IResponse::S409_CONFLICT, 'emailConflict');
		}

		$this->em->persist($token = new Token($user, 'setPassword', new DateTime('+24 hours')));

		$this->userCreatedSender->send($token->key, $user->email);

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
	 * Updates password of user identified by provided token key.
	 */
	public function actionUpdateUserByToken()
	{
		$this->removeExpiredTokens();

		/** @var Token $token */
		$token = $this->em->getRepository(Token::class)->findOneBy(['key' => $this->getQuery(['token', 'key'])]);

		if (!$token) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownToken', 'Unknown token key. Token maybe expired.');
		}

		if ($token->type !== 'setPassword') {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'invalidTokenType');
		}

		$token->user->setPassword($this->getPost('password'));


		$this->em->remove($token)->flush();

		$this->sendJson($this->mapUser($token->user));
	}

	/**
	 * Deletes single user entity.
	 * @param int $id
	 */
	public function actionDelete($id)
	{
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

	/**
	 * Removes all expired tokens, so they are not cumulating in database.
	 */
	private function removeExpiredTokens()
	{
		$this->em->getFilters()->disable('expiredTokenFilter');

		$this->em->createQuery('DELETE ' . Token::class . ' t WHERE t.expiration < ?0')
			->execute([new DateTime]);

		$this->em->getFilters()->enable('expiredTokenFilter');
	}

}
