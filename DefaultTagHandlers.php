<?php
namespace MediaWiki\Extension\BBCodeContent;
use Title;
use File;

class DefaultTagHandlers {
	public static function tagQuote( $content, $param ) {
		$out = '<blockquote'
			. ' style="background-color: #f7f7f7;'
			. ' border: 1px solid #ccc;'
			. ' margin: 12px 0;'
			. ' padding: 12px 20px;">';
		if ($param) {
			$out .= '<p style="font-weight: bold; margin-bottom: 6px">';
			$out .= wfMessage( 'bbcc-quote-author', $param )->escaped();
			$out .= '</p>';
		}
		$out .= $content;
		$out .= '</blockquote>';
		return $out;
	}
	public static function tagImg( $content, $param ) {
		$title = Title::newFromText($content, NS_FILE);
		if (!$title) {
			return DefaultTagHandlers::tagWiki('wiki', $content, '');
		} else if (!$title->exists()) {
			return DefaultTagHandlers::tagWiki('wiki', $title->getNsText() . ':' . $content, '');
		}
		$file = new File($title);
		$out = '<img src="';
		$out .= $file->getFullUrl();
		$out .= '"/>';
		return $out;
	}
	public static function tagList( $content, $param ) {
		if (ctype_digit($param)) {
			return '<ol start="' . $param . '">' . $content . '</ol>';
		}
		return '<ul>' . $content . '</ul>';
	}

	public static function tagWiki( $content, $param ) {
		$title = Title::newFromText($param ? $param : $content);
		if (!$title) return $content;
		$out = $title->isKnown() ? '<a href="' : '<a class="new" href="';
		$out .= $title->getLocalURL();
		$out .= '">';
		$out .= $content;
		$out .= '</a>';
		return $out;
	}

	public static function tagCode( $content, $param ) {
		if (class_exists(SyntaxHighlight::class) && $param) {
			$status = SyntaxHighlight::highlight(htmlspecialchars_decode($content), $param);
			if ($status->isGood()) {
				return $status->getValue();
			}
		} else if (class_exists(SyntaxHighlight_GeSHi::class) && $param) {
			$status = SyntaxHighlight_GeSHi::highlight(htmlspecialchars_decode($content), $param);
			if ($status->isGood()) {
				return $status->getValue();
			}
		}
		return '<pre class="mw-code">' . $content . '</pre>';
	}

}
