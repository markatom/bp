<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Kdyby\Doctrine\EntityManager;
use Model\Entity\Session;
use Nette\DI\Container;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context, SnippetAcceptingContext
{

	/** @var Container */
	private $dic;

	/** @var EntityManager */
	private $em;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
		$this->dic = require __DIR__ . '/../bootstrap.php';
		$this->em  = $this->dic->getByType(EntityManager::class);

		$loader = new Loader;
		$loader->loadFromDirectory(__DIR__ . '/../../fixtures');

		$executor = new ORMExecutor($this->em, new ORMPurger);
		$executor->execute($loader->getFixtures());
    }

	/**
	 * Activates session of given user.
	 * @Given /^I am "(?P<fullName>(?:[^"]|\\")*)"$/
	 */
	public function user($fullName)
	{
		/** @var EntityManager $em */
		$em = $this->dic->getByType(EntityManager::class);

		/** @var Session $session */
		$session = $em->getRepository(Session::class)->findOneBy(['user.fullName' => $fullName]);

		if (!$session) {
			throw new LogicException("Cannot find signed in user with name $fullName.");
		}

		$this->visitPath('/');
		$this->getSession()->setCookie('session-token', $session->token);
	}

	/**
	 * Opens welcome screen.
	 * @Given /^I am on the welcome screen$/
	 */
	public function welcomeScreen()
	{
		$this->visitPath('/');
		sleep(1);
	}

	/**
	 * Waits one seconds.
	 * @When /^I wait for a while$/
	 */
	public function wait()
	{
		sleep(1);
	}

}
