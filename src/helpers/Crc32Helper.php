<?php

namespace pjanser\craftdbextract\lib;

abstract class Crc32Helper
{
	private static function gf2_matrix_times(array &$mat, int $vec): int
	{
		$iMat = 0;
		$sum = 0;
		while ($vec) {
			if ($vec & 1) {
				$sum ^= $mat[$iMat];
			}
			$vec >>= 1;
			++$iMat;
		}
		return $sum;
	}

	private static function gf2_matrix_square(array &$square, array &$mat): void
	{
		for ($n = 0; $n < 32; ++$n) {
			$square[$n] = static::gf2_matrix_times($mat, $mat[$n]);
		}
	}

	public static function combine_crc32(int $crc1, int $crc2, int $len2): int
	{
		/** @var int */
		$n = 0;

		/** @var int[] even-power-of-two zeros operator */
		$even = [];
		/** @var int[] odd-power-of-two zeros operator */
		$odd = [];

		// degenerate case (also disallow negative lengths)
		if ($len2 <= 0) {
			return $crc1;
		}

		// put operator for one zero bit in odd
		$odd[0] = 0xedb88320; // CRC-32 polynomial

		/** @var int */
		$row = 1;
		for ($n = 1; $n < 32; ++$n) {
			$odd[$n] = $row;
			$row <<= 1;
		}

		// put operator for two zero bits in even
		static::gf2_matrix_square($even, $odd);

		// put operator for four zero bits in odd
		static::gf2_matrix_square($odd, $even);

		// apply len2 zeros to $crc1 (first square will put the operator for one
		// zero byte, eight zero bits, in even)
		do {
			// apply zeros operator for this bit of len2
			static::gf2_matrix_square($even, $odd);
			if ($len2 & 1) {
				$crc1 = static::gf2_matrix_times($even, $crc1);
			}
			$len2 >>= 1;

			// if no more bits set, then done
			if ($len2 === 0)
				break;

			// another iteration of the loop with odd and even swapped
			static::gf2_matrix_square($odd, $even);
			if ($len2 & 1) {
				$crc1 = static::gf2_matrix_times($odd, $crc1);
			}
			$len2 >>= 1;

			// if no more bits set, then done
		} while ($len2 !== 0);

		// return combined crc
		$crc1 ^= $crc2;
		return $crc1;
	}

	/**
	 * Calculates CRC32 in chunks to limit memory use
	 *
	 * @param  resource $fh
	 * @return int CRC32 of the complete stream
	 */
	public static function crc32FromStream($fh): int
	{
		\fseek($fh, 0, \SEEK_END);
		$fileSize = \ftell($fh);
		\fseek($fh, 0);

		$chunkSize = 8 * 1024 * 1024; // 8MB per chunk

		$crcTotal = null;

		while (
			\ftell($fh) < $fileSize
			&& false !== ($chunk = \fread($fh, $chunkSize))
		) {
			$crc = \crc32($chunk);

			if ($crcTotal === null) {
				$crcTotal = $crc;
			} else {
				$crcTotal = static::combine_crc32(
					$crcTotal,
					$crc,
					\mb_strlen($chunk, '8bit')
				);
			}
		}

		// rewind stream
		\fseek($fh, 0);

		return $crcTotal;
	}
}
