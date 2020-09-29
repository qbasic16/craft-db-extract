<?php
namespace pjanser\craftdbextract\services;

use Craft;
use craft\base\Component;
use craft\web\Response;

class DbService extends Component
{
    const ZLIB_PARAMS = [
        'level'  => 6,
        'window' => 15,
        'memory' => 9 // 1 - 9
    ];

    /**
     * Creates a backup file and returns its data
     *
     * Writes raw SQL or GZIP-encoded SQL to the output
     *
     * @return void
     */
    public function dump($useGz = false): Response
    {
        $response = Craft::$app->getResponse();
        $db = Craft::$app->getDb();

        $filePath = $db->backup();

        $fileNameOut = 'db.sql' . ($useGz ? '.gz' : '');
        $mimeOut = $useGz ? 'application/gzip' : 'text/plain';

        // open stream
        $fpOut = \fopen($filePath, 'rb');

        if ($useGz) {
            \stream_filter_append(
                $fpOut,
                'zlib.deflate',
                \STREAM_FILTER_WRITE,
                self::ZLIB_PARAMS
            );
        }

        // send file stream
        return $response->sendStreamAsFile(
            $fpOut,
            $fileNameOut,
            [
                'mimeType' => $mimeOut,
                'inline' => false
            ]
        );
    }
}
