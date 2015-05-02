<?php

namespace Presenter;

use Kdyby\Doctrine\EntityManager;
use Model\Entity\Session;
use Model\Entity\User;
use Nette\Http\IResponse;

/**
 * Ancestor for secured resources (only signed in user can request them).
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
abstract class SecuredPresenter extends ApiPresenter
{

	/** @var EntityManager @inject */
	public $em;

	/** @var User */
	protected $user;

	/**
	 * Performs authentication.
	 */
	public function startup()
    {
		$this->authenticate();
    }

	/**
	 * Validates the session and obtains an entity of signed in user.
	 */
	public function authenticate()
	{
		parent::startup();

		$token = $this->httpRequest->getHeader('X-Session-Token');

		if ($token === NULL) {
			$this->sendError(IResponse::S401_UNAUTHORIZED, 'missingSessionToken', "Missing session token in header 'X-Session-Token'.");
		}

		/** @var Session $session */
		$session = $this->em->getRepository(Session::class)->findOneBy(['token' => $token]);

		if (!$session) {
			$this->sendError(IResponse::S401_UNAUTHORIZED, 'unknownSessionToken', "Unknown session token supplied in 'X-Session-Token' header. Token maybe expired.");
		}

		$session->renew();
		$this->em->flush();

		$this->user = $session->user;
	}

}
