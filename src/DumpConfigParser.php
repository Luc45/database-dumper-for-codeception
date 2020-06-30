<?php

namespace DumpGenerator;

use Symfony\Component\VarExporter\VarExporter;

class DumpConfigParser {

	private $dump_location;
	private $dump_config;

	public function __construct( string $dump_location ) {
		$this->dump_location = $dump_location;
	}

	public function makeDumpConfig( array $dump_settings, array $pdo_settings ) {
		$dump_dir          = dirname( $this->dump_location );
		$dump_file         = basename( $this->dump_location );
		$this->dump_config = $dump_dir . '/' . $dump_file . '.php';

		$dump_settings = VarExporter::export( $dump_settings );
		$pdo_settings  = VarExporter::export( $pdo_settings );

		if ( file_exists( $this->dump_config ) ) {
			codecept_debug( '(GenerateDump) Skipping makeDumpConfig since file already exists. ' . $this->dump_config );
			return;
		}

		file_put_contents( $this->dump_config,
			<<<PHP
<?php
/**
 * @var array \$dump_settings Settings for the dumper.
 * @var array \$pdo_settings  Settings for the PDO that the Dumper uses.
 * @var Ifsnop\Mysqldump\Mysqldump \$dumper You can customize which tables are going to be used in the dump,
 *                                         as well as execute queries for which rows should go or not into the dump, etc.
 *
 * @example \$dumper->setTableLimits( [ 'users' => 300,'logs' => 50, 'posts' => 10 ] );
 *
 * @link https://github.com/ifsnop/mysqldump-php You can find more info about \$dumper, \$dumper_args and \$pdo_args in this link.
 *
 * @return array You should not change the generated return.
 */
\$dump_settings = $dump_settings;
\$pdo_settings  = $pdo_settings;

// Do not change the return.
return [ \$dumper, \$dump_settings, \$pdo_settings ];
PHP
		);
	}

	public function getDumpConfigFile() {
		return $this->dump_config;
	}
}