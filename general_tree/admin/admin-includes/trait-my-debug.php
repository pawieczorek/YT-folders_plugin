<?php

trait my__debug {

	function debug_console( $name, $output, $with_script_tags = true ) {

		$js_code = 'console.log(' . json_encode( $output, JSON_HEX_TAG ) .
		');';

		if ( $with_script_tags ) {
			$js_code = '<script>' . $js_code . '</script>';
		}

		$js_code1 = 'console.log(' . json_encode( $name, JSON_HEX_TAG ) .
		');';

		if ( $with_script_tags ) {
			$js_code1 = '<script>' . $js_code1 . '</script>';
		}

		echo $js_code1;
		echo $js_code;
	}


	function debug_file( $nazwa, $var ) {
        
		$myfile = file_put_contents( PLUGIN_DIR . '/logs.txt', print_r( $nazwa, true ) . ': ' . print_r( $var, true ) . "\n\n", FILE_APPEND | LOCK_EX );
	}

}
