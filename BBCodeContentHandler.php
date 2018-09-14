<?php
namespace MediaWiki\Extension\BBCodeContent;

use Content;
use IContextSource;
use TextContentHandler;

class BBCodeContentHandler extends TextContentHandler {

	public function __construct(
		$modelId = CONTENT_MODEL_BBCODE,
		$formats = [ CONTENT_FORMAT_TEXT ]
	) {
		parent::__construct( $modelId, $formats );
	}

	public function serializeContent( Content $content, $format = null ) {
		// No special logic needed; BBCodeContent just wraps the raw text.
		return parent::serializeContent( $content, $format );
	}

	public function unserializeContent( $text, $format = null ) {
		// No special logic needed; BBCodeContent just wraps the raw text.
		return new BBCodeContent( $text );
	}

	public function makeEmptyContent() {
		return new BBCodeContent( '' );
	}

	public function getActionOverrides() {
		// The standard edit page will work as a default for
		// any text-based content.
		return parent::getActionOverrides();
	}

	public function createDifferenceEngine( IContextSource $context,
		$old = 0, $new = 0, $rcid = 0,
		$refreshCache = false, $unhide = false
	) {
		return parent::createDifferenceEngine( $context, $old, $new, $rcid, $refreshCache, $unhide );
	}

	public function supportsSections() {
		return false;
	}

	public function supportsRedirects() {
		return false;
	}

	public function merge3( Content $oldContent, Content $myContent, Content $yourContent ) {
		return parent::merge3( $oldContent, $myContent, $yourContent );
	}
}
