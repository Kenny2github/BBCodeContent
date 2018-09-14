<?php
namespace MediaWiki\Extension\BBCodeContent;

class Hooks {

	/**
	 * Called directly after MediaWiki has processed extension.json
	 * @link https://www.mediawiki.org/wiki/Manual:Extension.json/Schema#callback
	 */
	public static function registrationCallback() {
		define( 'CONTENT_MODEL_BBCODE', 'BBCode' );
	}
}
