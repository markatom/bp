<?php

namespace Presenter;

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

	/** @var EntityManager */
	private $em;

	/**
	 * @param EntityManager $em
	 */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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

		$session = new Session($user, $this->getPost('longLife'));

		$this->em->persist($session)->flush();

		$this->sendJson([
			'token' => $session->token,
			'user'  => [
				'fullName' => $user->fullName,
				'email'    => $user->email,
			],
		], IResponse::S201_CREATED);
	}

	/**
	 * Terminates session.
	 */
	public function actionDelete()
	{
		$token = $this->httpRequest->getHeader('X-Session-Token');

		if ($token === NULL) {
			$this->sendBadRequest("Missing header 'X-Session-Token' identifying current session.");
		}

		$session = $this->em->getRepository(Session::class)->findBy(['token' => $token]);

		if (!$session) {
			$this->sendBadRequest("Unknown token supplied in 'X-Session-Token' header.");
		}

		$this->em->remove($session)->flush();

		$this->sendEmpty();
	}

}
