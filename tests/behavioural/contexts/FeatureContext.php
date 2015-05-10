<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Mink\Exception\ResponseTextException;
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

	const TEST_CONFIG = '/../test.neon';

	const LOCAL_CONFIG = '/../../../app/config/local.neon';

	const BACKUP_SUFFIX = '.backup';

	/** @var Container */
	private $dic;

	/** @var EntityManager */
	private $em;

	/** @var Loader */
	private $loader;

	/** @var ORMExecutor */
	private $executor;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
		$this->dic      = require __DIR__ . '/../bootstrap.php';
		$this->em       = $this->dic->getByType(EntityManager::class);
		$this->loader   = new Loader;
		$this->executor = new ORMExecutor($this->em, new ORMPurger);

		$this->loader->loadFromDirectory(__DIR__ . '/../../fixtures');
	}

	/**
	 * Loads fixtures.
	 * @BeforeScenario
	 */
	public function beforeScenario()
	{
		$this->executor->execute($this->loader->getFixtures());
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

		$this->setSessionToken($session->token);
		$this->visitPath('/');
		sleep(1);
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
	 * Waits x seconds.
	 * @When /^I wait for "(?P<delay>(?:[^"]|\\")*)" seconds?$/
	 */
	public function wait($delay)
	{
		sleep($delay);
	}

	/**
	 * @When /^I follow "(?:[^"]|\\")*" with xpath "(?P<xpath>(?:[^"]|\\")*)"$/
	 */
	public function followXpath($xpath)
	{
		$element = $this->getSession()->getPage()->find('xpath', $xpath);

		if (!$element) {
			throw new LogicException("Could not find any element matching xpath '$xpath'.");
		}

		$element->click();
		sleep(1);
	}

	public function clickLink($link)
	{
		parent::clickLink($link);
		sleep(1);
	}

	public function pressButton($button)
	{
		parent::pressButton($button);
		sleep(1);
	}

	/**
	 * @Then /^I should see "(?P<items>(?:[^"]|\\")*)" in that order$/
	 */
	public function assertPageContainsOrderedItems($items)
	{
		$actual = $this->getSession()->getPage()->getText();
		$actual = preg_replace('/\s+/u', ' ', $actual);

		$previous = NULL;
		foreach (explode(',', $items) as $text) {
			$text  = trim($text);
			$regex = '/' . preg_quote($text, '/') . '/ui';

			if (preg_match($regex, $actual, $matches, PREG_OFFSET_CAPTURE)) {
				if ($previous !== NULL && $matches[0][1] <= $previous[1]) {
					$message = sprintf('The text "%s" was not followed by text "%s" in the text of the current page.', $previous[0], $text);
					throw new ResponseTextException($message, $this->getSession());
				}
				$previous = $matches[0];

			} else {
				$message = sprintf('The text "%s" was not found anywhere in the text of the current page.', $text);
				throw new ResponseTextException($message, $this->getSession());
			}
		}
	}

	/**
	 * @Then /^I should not see any of "(?P<items>(?:[^"]|\\")*)"$/
	 */
	public function assertPageNotContainsItems($items)
	{
		$actual = $this->getSession()->getPage()->getText();
		$actual = preg_replace('/\s+/u', ' ', $actual);

		foreach (explode(',', $items) as $text) {
			$regex  = '/' . preg_quote(trim($text), '/') . '/ui';
			if (preg_match($regex, $actual)) {
				$message = sprintf('The text "%s" appears in the text of this page, but it should not.', $text);
				throw new ResponseTextException($message, $this->getSession());
			}
		}
	}

	/**
	 * Sets session cookie and redirects to '/'.
	 * @param string|NULL $token
	 */
	private function setSessionToken($token)
	{
		// redirect to app in order to set cookie
		$this->visitPath('/');

		// cookie must be set via javascript
		// native way will force persist cookie even if application tries to remove it
		$this->getSession()->executeScript("document.cookie = 'session-token=$token'");
	}

}
