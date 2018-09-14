<?php
namespace MediaWiki\Extension\BBCodeContent;

use TextContent;
use Title;
use ParserOptions;
use ParserOutput;

function bbparse($string, $tags) {
	$tagnames = [];
	foreach (array_keys($tags) as $tag) {
		$tagnames[] = preg_quote($tag);
	}
	$matches = preg_split('%(\n)|(\[/?(?:' . implode('|', $tagnames) . '|[^=\]]+))(?:(=)([^\]]+))?\]%', $string, NULL, PREG_SPLIT_DELIM_CAPTURE);
	$i = 0;
	$stack = [];
	$funcstack = [];
	$res = '';
	while ($i < count($matches)) {
		$lastEl = end($stack);
		if ($matches[$i][0] == '[') {
			$tag = substr($matches[$i], 1);
			if ($tag[0] == '/' && $lastEl == substr($tag, 1)) {
				$tag = substr($tag, 1);
				$lastFn = end($funcstack);
				array_pop($stack);
				if (is_callable($tags[$tag]) && $lastFn && $lastFn[0] == (count($stack) + 1)) {
					$cont = substr($res, $lastFn[1]);
					$res = substr($res, 0, $lastFn[1]);
					$res .= call_user_func($tags[$tag], trim($cont), $lastFn[2]);
					array_pop($funcstack);
				} else {
					$res .= $tags[$tag][1];
				}
			} else {
				$stack[] = $tag;
				$funcq = is_callable($tags[$tag]);
				if ($funcq) {
					$forlater = [count($stack), strlen($res), ''];
				} else {
					$open = $tags[$tag][0];
				}
				if ($matches[$i + 1] == '=') {
					$i += 2;
					if ($funcq) {
						$forlater[2] = htmlspecialchars(trim($matches[$i]));
					} else {
						$open = str_replace('{PARAM}', htmlspecialchars($matches[$i]), $open);
					}
				}
				if ($funcq) {
					$funcstack[] = $forlater;
				} else {
					$res .= $open;
				}
			}
		} else if ($matches[$i] == "\n" && lastEl && is_array($tags[$lastEl]) && $tags[$lastEl][2]) {
			$res .= $tags[$lastEl][1];
			array_pop($stack);
		} else {
			$res .= htmlspecialchars($matches[$i]);
		}
		$i += 1;
	}
	$res = str_replace("\n", '<br/>', $res);
	return $res;
}


class BBCodeContent extends TextContent {

	public function __construct( $text, $model_id = CONTENT_MODEL_BBCODE ) {
		parent::__construct( $text, $model_id );
	}

	public function fillParserOutput(
		Title $title,
		$revid,
		ParserOptions $options,
		$generateHtml,
		ParserOutput &$output
	) {
		$out = $this->bbhtml();
		if (class_exists(SyntaxHighlight::class) || class_exists(SyntaxHighlight_GeSHi::class)) {
			$output->addModuleStyles( 'ext.pygments' );
		}
		$output->setText($output->getRawText() . $out);
		return $output;
	}

	public function bbhtml() {
		global $wgBBCCTags;
		$out = $this->getNativeData();
		$out = bbparse($out, $wgBBCCTags);
		return $out;
	}

	public function isEmpty() {
		// Determines whether this content can be considered empty.
		// For BBCode, we check the tagless content for emptiness:

		$text = trim( preg_replace('%\[[^\]]+\]%', '', $this->getNativeData() ) );
		return empty($text);
	}

	public function isCountable( $hasLinks = null ) {
		// Determines whether this content should be counted as a "page" for the wiki's statistics.
		// Here, we require it to be not-empty and have one internal link:
		return !$this->isEmpty() && strpos($this->getNativeData(), '[wiki' !== false);
	}

	public function isValid() {
		// This is a last line of defense against storing invalid data.
		// It can be used to check validity, as an alternative to doing so
		// in prepareSave().
		//
		// Checking here has the advantage that this is ALWAYS called before
		// the content is saved to the database, no matter whether the content
		// was edited, imported, restored, or what.
		//
		// The downside is that it's too late here for meaningful interaction
		// with the user, we can just abort the save operation, casing an internal
		// error.

		return parent::isValid();
	}

	public function getTextForSearchIndex() {
		// Should return text relevant to the wiki's search index, for instance by stripping tags:
		return preg_replace('\[[^\]]+\]', '', $this->getNativeData() );
	}

	public function convert( $toModel, $lossy = '' ) {
		// Implement conversion to other content models.
		// Text based models can per default be converted to all other text based models.

		return parent::convert( $toModel, $lossy );
	}

}
