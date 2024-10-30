<?php

/**
 * view template loader.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Template_Loader {

	public function __construct() {
	}

	/**
	 * @param string $name
	 * @param array $data
	 */
	public static function render( $name, $data = array() ) {
		$include_file = BLFPST::plugin_dir( sprintf( 'views/%s.php', $name ) );

		/** @noinspection PhpIncludeInspection */
		include $include_file;
	}
}
