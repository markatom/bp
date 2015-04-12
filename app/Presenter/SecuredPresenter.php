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
class SecuredPresenter extends ApiPresenter
{

	/** @var EntityManager @inject */
	public $em;

	/** @var User */
	protected $user;

	/**
	 * Validates session and obtains user entity.
	 */
	public function startup()
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
