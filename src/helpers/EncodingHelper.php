<?php

namespace pjanser\craftdbextract\lib;

abstract class EncodingHelper
{
	public static function toISO_8859_1(string $str): string
	{
		$currentEncoding = \mb_detect_encoding(
			$str,
			'UTF-8, ISO-8859-1, ISO-8859-15',
			true
		);
		return \mb_convert_encoding($str, 'ISO-8859-1', $currentEncoding);
	}
}
