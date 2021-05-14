<?php

namespace pjanser\craftdbextract\lib;

abstract class GzipHelper
{
	/**
	 * Writes the GZip header to the stream
	 *
	 * @param  resource $fh
	 * @param  string $filename
	 * @return void
	 */
	public static function writeHeader($fh, ?string $filename = null): void
	{
		$now = \time();

		$header = [
			0x1F, // ID1, 1 byte, fixed value
			0x8B, // ID2, 1 byte, fixed value
			0x08, // CM, 1 byte,
			// FLG, 1 byte, the header flags
			0x00 // FTEXT, bit 0, is ASCII only?
				| 0x00 // FHCRC, bit 1, has CRC16 of gzip header?
				| 0x00 // FEXTRA, bit 2
				| 0x00 // FNAME, bit 3, includes the file name?
				| 0x00 // FCOMMENT, bit 4, includes a comment?
				| 0x00, // reserved flags, bits 5-7, must be 0
			// MTIME, 4 bytes
					0x00,
					0x00,
					0x00,
					0x00,
			// ($now >> 0) & 0xff, // MTIME, byte 0
			// ($now >> 8) & 0xff, // MTIME, byte 1
			// ($now >> 16) & 0xff, // MTIME, byte 2
			// ($now >> 24) & 0xff, // MTIME, byte 3
			0x00, // XFL, 1 byte
			0xff, // OS, 1 byte, unknown
		];

		// if (!empty($filename)) {
		// 	$header[3] |= 0x08; // set FNAME flag
		// 	$filenameIso = EncodingHelper::toISO_8859_1($filename) . "\0";
		// }

		$headerStr = \join('', \array_map('chr', $header));
		$headerLen = \count($header);

		// if (!empty($filename)) {
		// 	$headerStr .= $filenameIso;
		// 	$headerLen += \mb_strlen($filenameIso, '8bit');
		// }

		\fwrite($fh, $headerStr, $headerLen);
	}

	public static function writeTail($fh, int $crc32, int $fileSize): void
	{
		\fwrite($fh, \pack('V', $crc32 & 0xffffffff));
		\fwrite($fh, \pack('V', $fileSize & 0xffffffff));
	}
}
