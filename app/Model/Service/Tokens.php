<?php

namespace Model\Service;

use DateTime;
use Kdyby\Doctrine\EntityManager;
use Model\Entity\Token;
use Model\Entity\User;
use Nette\Object;

/**
 * Manages tokens.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class Tokens extends Object
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
	 * @param User $user
	 * @param string $type
	 * @param string $expiration
	 * @return Token
	 */
	public function create(User $user, $type, $expiration)
	{
		$token = new Token($user, $type, new DateTime($expiration));

		$this->em->persist($token)->flush($token);

		return $token;
	}

	/**
	 * @param string $key
	 * @param string $type
	 * @return Token|NULL
	 */
	public function get($key, $type)
	{
		$this->removeExpiredTokens();

		$token = $this->em->getRepository(Token::class)->findOneBy(['key' => $key]);

		return $token && $token->type === $type
			? $token : NULL;
	}

	/**
	 * @param Token $token
	 */
	public function delete(Token $token)
	{
		$this->em->remove($token)->flush($token);
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
