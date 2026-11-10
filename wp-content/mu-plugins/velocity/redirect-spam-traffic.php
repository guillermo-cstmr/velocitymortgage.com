<?php

if ( isset( $_SERVER['HTTP_REFERER'] ) && strpos( $_SERVER['HTTP_REFERER'], 'accept-bitcoinser-plugin.info' ) !== false ) {

	header( 'HTTP/1.0 403 Forbidden' );
	die();

}
