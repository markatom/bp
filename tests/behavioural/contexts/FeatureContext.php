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
use Nette\Neon\Neon;

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
	 * Creates local config with test database for tested server application.
	 * @BeforeSuite
	 */
	public static function beforeSuite()
	{
		$test  = Neon::decode(file_get_contents(__DIR__ . self::TEST_CONFIG));
		$local = Neon::decode(file_get_contents(__DIR__ . self::LOCAL_CONFIG));

		$local['doctrine']['dbname'] = $test['doctrine']['dbname'];

		rename(__DIR__ . self::LOCAL_CONFIG, __DIR__ . self::LOCAL_CONFIG . self::BACKUP_SUFFIX);
		file_put_contents(__DIR__ . self::LOCAL_CONFIG, Neon::encode($local, Neon::BLOCK));
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
	 * Undo changed local config.
	 * @AfterSuite
	 */
	public static function afterSuite()
	{
		rename(__DIR__ . self::LOCAL_CONFIG . self::BACKUP_SUFFIX, __DIR__ . self::LOCAL_CONFIG);
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
		$this->visitPath('/');
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

	/**
	 * @When /^I follow "(?:[^"]|\\")*" with xpath "(?P<xpath>(?:[^"]|\\")*)"$/
	 */
	public function followXpath($xpath)
	{
		$this->getSession()->getPage()->find('xpath', $xpath)->click();
	}

}
