<?php
namespace pjanser\craftdbextract\services;

use Craft;
use craft\base\Component;
use yii\web\ServerErrorHttpException;

class DbService extends Component
{
    /**
     * Creates a backup file and returns its data
     *
     * @return string Raw or GZIP encoded SQL export
     */
    public function dump($useGz = false): string
    {
        $db = Craft::$app->getDb();

        $file = $db->backup();

        $data = \file_get_contents($file);
        if ($useGz) {
            $gzData = \gzencode($data);
            if ($gzData === false) {
                throw new ServerErrorHttpException('Failed to gzip encode the file');
            }
            $data = $gzData;
        }
        // delete backup file to prevent cluttering the folder
        \unlink($file);

        return $data;
    }
}
