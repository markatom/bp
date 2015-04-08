<?php

namespace Presenter;

use Nette\Http\IResponse;
use Nette\Object;

/**
 * @todo Fill desc.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class UserPresenter extends SecuredPresenter
{

	/**
	 * Reads information about logged user.
	 */
    public function readMe()
	{
		$this->sendJson([
			'fullName' => $this->user->fullName,
			'email'    => $this->user->email,
		], IResponse::S200_OK);
	}

}
