<?php

namespace pjanser\craftdbextract\controllers;

use Craft;
use craft\web\Controller;
use craft\web\Response;
use pjanser\craftdbextract\Craftdbextract;
use yii\filters\auth\HttpBasicAuth;

class DbExportController extends Controller
{
    protected $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['basicAuth'] = [
            'class' => HttpBasicAuth::class,
            'auth' => function ($username, $password) {
                $user = Craft::$app->getUsers()->getUserByUsernameOrEmail($username);

                // Delay randomly between 0 and 500 ms.
                usleep(random_int(0, 500000));

                if (!$user || $user->password === null) {
                    // Delay again to match $user->authenticate()'s delay
                    Craft::$app->getSecurity()->validatePassword(
                        'p@ss1w0rd',
                        '$2y$13$nj9aiBeb7RfEfYP3Cum6Revyu14QelGGxwcnFUKXIrQUitSodEPRi'
                    );
                    return null;
                }

                return $user->authenticate($password) ? $user : null;
            }
        ];

        return $behaviors;
    }

    public function actionIndex(): Response
    {
        $this->requireAdmin();

        $useGz = $this->request->getQueryParam('compression', '') === 'gzip';

        [
            $fh,
            $fsize,
            $crc32,
            $filename,
            $mimeType,
        ] = Craftdbextract::$plugin->getDb()->dump($useGz);

        if ($fh === false) {
            return $this->response->setStatusCode(500, 'Unable to get file handle');
        }

        return $this->response->sendStreamAsFile(
            $fh,
            $filename,
            [
                // 'fileSize' => $fsize,
                'mimeType' => $mimeType,
            ]
        );
    }
}
