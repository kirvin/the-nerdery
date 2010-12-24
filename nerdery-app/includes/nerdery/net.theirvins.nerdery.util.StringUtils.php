<?php

class StringUtils {

	/**
	 * 
	 * @param string $txt
	 */
	public static function formatUserText ($txt) {
		// \b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))
		return nl2br ($txt);
	}

}