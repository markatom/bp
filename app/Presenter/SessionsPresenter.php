<?php

namespace Presenter;

use DateTime;
use Kdyby\Doctrine\EntityManager;
use Model\Entity\Session;
use Model\Entity\User;
use Nette\Http\IResponse;

/**
 * @todo Fill desc.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class SessionsPresenter extends ApiPresenter
{

	/** @var EntityManager @inject */
	public $em;

	/**
	 * Removes all expired sessions, so they are not cumulating in database.
	 */
	public function startup()
	{
		parent::startup();

		$this->em->getFilters()->disable('expiredSessionFilter');

		$this->em->createQuery('DELETE ' . Session::class . ' s WHERE s.expiration < ?0')
			->execute([new DateTime]);

		$this->em->getFilters()->enable('expiredSessionFilter');
	}

	/**
	 * Performs authentication and establishes session.
	 */
	public function actionCreate()
	{
		$email = $this->getPost(['user', 'email']);

		/** @var User $user */
		$user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

		if (!$user) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'unknownEmail');
		}

		if (!$user->authenticate($this->getPost(['user', 'password']))) {
			$this->sendError(IResponse::S401_UNAUTHORIZED, 'incorrectPassword');
		}

		$session = new Session($user, $this->getPost('longLife', FALSE));

		$this->em->persist($session)->flush();

		$this->sendJson(self::mapSession($session), IResponse::S201_CREATED);
	}

	/**
	 * Retrieves current session.
	 */
	public function actionReadCurrent()
	{
		$token = $this->httpRequest->getHeader('X-Session-Token');

		if ($token === NULL) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'missingSessionToken', "Missing header 'X-Session-Token' identifying current session.");
		}

		/** @var Session $session */
		$session = $this->em->getRepository(Session::class)->findOneBy(['token' => $token]);

		if (!$session) {
			$this->sendEmpty();
		}

		$this->sendJson(self::mapSession($session));
	}

	/**
	 * Terminates current session.
	 */
	public function actionDeleteCurrent()
	{
		$token = $this->httpRequest->getHeader('X-Session-Token');

		if ($token === NULL) {
			$this->sendError(IResponse::S400_BAD_REQUEST, 'missingSessionToken', "Missing header 'X-Session-Token' identifying current session.");
		}

		/** @var Session $session */
		$session = $this->em->getRepository(Session::class)->findOneBy(['token' => $token]);

		if ($session) {
			$this->em->remove($session)->flush();
		}

		$this->sendEmpty();
	}

	/**
	 * Maps given session to an array.
	 * @param Session $session
	 * @return array
	 */
	public static function mapSession(Session $session)
	{
		return [
			'token'    => $session->token,
			'longLife' => $session->longLife,
			'user'     => UsersPresenter::mapUser($session->user),
		];
	}

}
