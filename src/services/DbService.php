<?php

namespace pjanser\craftdbextract\services;

use Craft;
use craft\base\Component;
use Exception;
use pjanser\craftdbextract\helpers\Crc32Helper;

class DbService extends Component
{
    /**
     * Creates a backup file and returns its data as a binary resource
     *
     * @param bool $useGz Use GZip compression?
     * @return resource|false The open resource for reading (binary mode)
     */
    public function dump(bool $useGz = false)
    {
        $db = Craft::$app->getDb();

        try {
            $filePath = $db->backup();
            $fileName = \basename($filePath);

            $fileNameOut = $fileName . ($useGz ? '.gz' : '');
            $mimeOut = $useGz ? 'application/gzip' : 'text/plain';

            // open DB backup from Craft in readonly & binary mode
            $fh = \fopen($filePath, 'r');

            // unable to open backup file
            if ($fh === false) {
                throw new Exception('Unable to open file');
            }

            // get original file size
            \fseek($fh, 0, \SEEK_END);
            $fileSize = \ftell($fh);
            \fseek($fh, 0);

            if ($useGz) {
                // get CRC32
                $crc32 = Crc32Helper::crc32FromStream($fh);

                $deflateCtx = \deflate_init(
                    \ZLIB_ENCODING_GZIP,
                    [
                        'level' => 9,
                        'memory' => 8,
                        'window' => 15
                    ]
                );

                $fhCompr = \fopen('php://temp', 'w');

                $chunkSize = 8 * 1024 * 1024; // 8MB per chunk

                while (($chunk = \fread($fh, $chunkSize))) {
                    \fwrite(
                        $fhCompr,
                        \deflate_add($deflateCtx, $chunk, \ZLIB_NO_FLUSH)
                    );
                }
                \fwrite(
                    $fhCompr,
                    \deflate_add($deflateCtx, '', \ZLIB_FINISH)
                );

                \fclose($fh);

                \fseek($fhCompr, 0);

                $fh = $fhCompr;

                // get size of compressed file
                \fseek($fh, 0, \SEEK_END);
                $fileSize = \ftell($fh);
                \fseek($fh, 0);
            }

            return [
                $fh, // resource handle for output
                $fileSize,
                $crc32,
                $fileNameOut, // filename
                $mimeOut // mimeType
            ];
        } catch (Exception $ex) {
            return [
                false, // resource handle for output
                0, // file size of output file
                null, // CRC32 of original SQL file
                null, // filename
                null // mimeType
            ];
        }
    }
}
