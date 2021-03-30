<?php

namespace pjanser\craftdbextract\services;

use Craft;
use craft\base\Component;
use Exception;

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
            $fh = \fopen($filePath, 'rb');

            // unable to open backup file
            if ($fh === false) {
                throw new Exception('Unable to open file');
            }
        } catch (Exception $ex) {
            return [
                false, // resource handle for output
                null, // filename
                null // mimeType
            ];
        }

        if ($useGz) {
            // attach gzip compression to stream
            \stream_filter_append(
                $fh,
                'zlib.deflate',
                \STREAM_FILTER_READ,
                [
                    'level'  => 6,
                    'window' => 15,
                    'memory' => 9 // 1 - 9
                ]
            );
        }

        return [
            $fh, // resource handle for output
            $fileNameOut, // filename
            $mimeOut // mimeType
        ];
    }
}
