<?php

/**
  * Miscellaneous helper functions that don't fit anywhere else.
  *
  * @file
  * @author Nmlgc
  */

class TPCUtil {
	/** 
	  * Python's dict.get in PHP
	  *
	  * @param mixed $element Dictionary element
	  * @param mixed $default Default value if element is not present
	  * @return mixed $element or $default
	  */
	public static function dictGet( &$element, $default = null ) {
		return isset( $element ) ? $element : $default;
	}

	/**
	  * Normalizes a hook name.
	  *
	  * @param string &$hook Hook name
	  * @return string Normalized hook name.
	  */
	public static function normalizeHook( $hook ) {
		// Normalize hook name... this should totally do for now
		$hook = strtolower( $hook );
		$hook = preg_replace( '/ /', '_', $hook );
		return $hook;
	}

	/**
	  * Checks whether an array is associative or numeric.
	  *
	  * @param array &$array The array to check.
	  * @return int > 0: associative, = 0: numeric, < 0: both
	  */
	public static function isAssoc( &$array ) {
		if ( !is_array( $array ) ) {
			return 0;
		}
		return count( array_filter( array_keys( $array ), 'is_string' ) );
	}

	/**
	  * Sanitizes a wikitext string for in-game display.
	  *
	  * @param string &$param The string to sanitize.
	  * @param bool $escape_percents Keep literal percent signs for a printf format string.
	  * @return string Sanitized string.
	  */
	public static function sanitize( &$param, $escape_percents = true ) {
		/**
		  * Remove translation markup. And yes, we use regex to do it.
		  * Justification:
		  * * The source format is plaintext, not XML. In fact, this is the only
		  *   instance of XML tags being used inside translatable strings.
		  * * Thus, using full-fledged XML parsers is counter-productive. PHP's
		  *   DOM, for example, ignores everything up to the first HTML tag.
		  *   Should we build some tags around the text by default just to work
		  *   around this now?
		  * * Some of the translation units extend to more template parameters
		  *   and we might not even have an opening or closing tag due to this.
		  * * Even worse, DOM might get confused with our layout markup... and
		  *   by working around *that*, we have ultimately defeated any reason
		  *   to use DOM in the first place.
		  */
		$param = preg_replace( '~<translate>\n~', '', $param );
		$param = preg_replace( '~</translate>~', '', $param );
		$param = preg_replace( '~<!--T\:(.*?)-->\n~', '', $param );

		// HTML comments
		$param = preg_replace( '/<!--(.*?)-->/', '', $param );

		// Remove {{lang}} wrappers
		$REGEX_LANG_PAT = '/\{\{\s*lang*\s*\|.*?\|\s*(.*?)\s*\}\}/i';
		$REGEX_LANG_REP = '\1';
		$param = preg_replace( $REGEX_LANG_PAT, $REGEX_LANG_REP, $param );
		// &nbsp;
		$param = preg_replace( '/&nbsp;/', json_decode( '"\u00a0"' ), $param );
		// MediaWiki markup
		$param = preg_replace( "/'''''(.*?)'''''/", '<bi$\1>', $param );
		$param = preg_replace( "/'''(.*?)'''/", '<b$\1>', $param );
		$param = preg_replace( "/''(.*?)''/", '<i$\1>', $param );
		if ( $escape_percents ) {
			$param = preg_replace( '/%/', '%%', $param );
		}
		// Do more MediaWiki stuff...
		return $param;
	}
}
