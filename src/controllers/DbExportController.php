<?php
namespace pjanser\craftdbextract\controllers;

use Craft;
use craft\web\Controller;
use craft\web\Response;
use pjanser\craftdbextract\Craftdbextract;

class DbExportController extends Controller
{
    public function actionIndex(): string
    {
        $response = Craft::$app->getResponse();
        $response->formatters = [Response::FORMAT_RAW ];
        $headers = $response->getHeaders();
        $headers->set('Content-Type', 'text/plain');

        return Craftdbextract::$plugin->getDb()->dump();
    }
}
