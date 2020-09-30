<?php
namespace pjanser\craftdbextract\services;

use Craft;
use craft\base\Component;
use craft\web\Response;

class DbService extends Component
{
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
                [
                    'level'  => 6,
                    'window' => 15,
                    'memory' => 9 // 1 - 9
                ]
            );
        }

        fseek($fpOut, 0, SEEK_END);
        $fileSize = ftell($fpOut);

        $response->headers->set('Content-Type', 'text/plain');
        $response->format = Response::FORMAT_RAW;
        $response->content = $fileSize;
        return $response;

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
