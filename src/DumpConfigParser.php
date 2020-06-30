<?php

namespace DumpGenerator;

use Symfony\Component\VarExporter\VarExporter;

class DumpConfigParser {

	private $dump_location;
	private $dump_config;

	public function __construct( string $dump_location ) {
		$this->dump_location = $dump_location;
	}

	/**
	 * @param array $dump_settings
	 * @param array $pdo_settings
	 *
	 * @return bool Whether the dump config is new or existing.
	 */
	public function makeDumpConfig( array $dump_settings, array $pdo_settings ): bool {
		$dump_dir          = dirname( $this->dump_location );
		$dump_file         = basename( $this->dump_location );
		$this->dump_config = $dump_dir . '/' . $dump_file . '.php';

		$dump_settings = VarExporter::export( $dump_settings );
		$pdo_settings  = VarExporter::export( $pdo_settings );

		if ( file_exists( $this->dump_config ) ) {
			// Config already exists.
			false;
		}

		file_put_contents( $this->dump_config,
			<<<PHP
<?php
/**
 * @var array \$dump_settings Settings for the dumper.
 * @var array \$pdo_settings  Settings for the PDO that the Dumper uses.
 * @var Ifsnop\Mysqldump\Mysqldump \$dumper A Dumper instance for further control. Check everything
 *                                          you can do in the link bellow.
 *
 * @link https://github.com/ifsnop/mysqldump-php You can find more info about \$dumper, \$dump_settings and \$pdo_settings in this link.
 *
 * @example \$dumper->setTableLimits( [ 'users' => 300,'logs' => 50, 'posts' => 10 ] );
 *
 * @return array You should not change the generated return.
 */

\$dump_settings = $dump_settings;

\$pdo_settings  = $pdo_settings;

// Do not change the return.
return [ \$dumper, \$dump_settings, \$pdo_settings ];
PHP
		);

		// New config
		return true;
	}

	public function getDumpConfigFile() {
		return $this->dump_config;
	}
}
