<?php

namespace DumpGenerator;

use Codeception\CustomCommandInterface;
use Codeception\Module\Db;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ifsnop\Mysqldump as IMysqldump;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Configuration;

class GenerateDump extends Command implements CustomCommandInterface {

	use \Codeception\Command\Shared\FileSystem;
	use \Codeception\Command\Shared\Config;

	protected function configure() {
		$this->setDefinition(
			[
				new InputArgument( 'suite', InputArgument::REQUIRED, 'suite that uses the Db module (or WPDb).' ),
			]
		);
		parent::configure();
	}

	public static function getCommandName() {
		return 'dump';
	}

	public function execute( InputInterface $input, OutputInterface $output ) {
		try {
			$suite_config = $this->getSuiteConfig( $input->getArgument( 'suite' ) );
			$db_module    = $this->findDbModule( $suite_config );
			$this->validateDbModule( $db_module );
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return 1;
		}

		$dsn          = $db_module->_getConfig( 'dump_dsn' );
		$user         = $db_module->_getConfig( 'dump_user' );
		$password     = $db_module->_getConfig( 'dump_password' );
		$dumpLocation = $this->getDumpLocation( $db_module );

		try {
			$dump = new IMysqldump\Mysqldump( $dsn, $user, $password );
			$dump->start( $dumpLocation );
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>mysqldump-php error: %s</error>', $e->getMessage() ) );

			return 1;
		}

		return 0;
	}

	private function findDbModule( array $suite_config ) {
		$enabled_modules = Configuration::modules( $suite_config );
		$di              = new Di();
		$moduleContainer = new ModuleContainer( $di, $suite_config );
		$dbModule        = null;

		foreach ( $enabled_modules as $enabled_module ) {
			$module = $moduleContainer->create( $enabled_module );
			if ( $module instanceof Db ) {
				return $module;
			}
		}

		throw new \RuntimeException( 'Could not find any modules that are an instance of \Codeception\Module\Db in this suite.' );
	}

	private function validateDbModule( Db $db_module ) {
		$required_parameters = [ 'dump_dsn', 'dump_user', 'dump_password', 'dump' ];
		$parameters          = $db_module->_getConfig();

		foreach ( $required_parameters as $p ) {
			if ( ! array_key_exists( $p, $parameters ) ) {
				throw new \DomainException( sprintf( '<error>The module "%s" need to set the parameter "%s".</error>', $db_module->_getName(), $p ) );
			}
		}
	}

	private function getDumpLocation( Db $db_module ) {
		$dump = $db_module->_getConfig( 'dump' );

		if ( ! is_string( $dump ) ) {
			throw new \RuntimeException( '<error>The dump argument must be a string for GenerateDump to work.</error>' );
		}

		// Convert it to absolute path if not absolute already.
		// Eg: tests/data/dump.sql -> /var/www/tests/data/dump.sql
		if ( $dump[0] !== '/' ) {
			$dump = rtrim( Configuration::projectDir(), '/' ) . '/' . $dump;
		}

		if ( ! is_writeable( dirname( $dump ) ) ) {
			throw new \RuntimeException( sprintf( '<error>The directory of the dump is not writeable by PHP. (%s)</error>', dirname( $dump ) ) );
		}

		return $dump;
	}
}
