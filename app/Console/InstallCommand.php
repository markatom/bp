<?php

namespace Console;

use Kdyby\Doctrine\EntityManager;
use Model\Entity\Role;
use Model\Entity\User;
use Nette\Neon\Neon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @todo Fill desc.
 *
 * @author Tomáš Markacz <tomas@markacz.com>
 */
class InstallCommand extends Command
{

	/** @var QuestionHelper */
	private $questionHelper;

	/** @var EntityManager */
	private $em;

	/**
	 * Configures the command.
	 */
	public function configure()
	{
		$this->setName('app:install')
			->setDescription('Prepares application to be operational.');
    }

	/**
	 * Obtains needed dependencies.
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	public function initialize(InputInterface $input, OutputInterface $output)
	{
		$this->questionHelper = $this->getHelper('question');
		$this->em             = $this->getHelper('container')->getByType(EntityManager::class);
	}

	/**
	 * Installs application.
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	public function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('Creating default user roles.');

		$this->em->persist($admin = new Role('admin', 'Administrátor'));
		$this->em->persist(new Role('employee', 'Zaměstnanec'));

		$output->writeln(PHP_EOL . 'In order to manage users in system, initial admin account must be created.');

		$this->em->persist(new User(
			$this->questionHelper->ask($input, $output, new Question("\tFull name: ")),
			$this->questionHelper->ask($input, $output, new Question("\tEmail address: ")),
			$this->questionHelper->ask($input, $output, (new Question("\tPassword: "))->setHidden(TRUE)),
			$admin
		));

		$this->em->flush();

		$output->writeln(PHP_EOL . '<info>Application successfully installed.</info>');

		return 0;
	}

}
