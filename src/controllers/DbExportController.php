<?php
namespace pjanser\craftdbextract\controllers;

use Craft;
use craft\web\Controller;
use craft\web\Response;
use pjanser\craftdbextract\Craftdbextract;
use yii\filters\auth\HttpBasicAuth;

class DbExportController extends Controller
{
    protected $allowAnonymous = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['basicAuth'] = [
            'class' => HttpBasicAuth::className(),
            'auth' => function ($username, $password) {
                $user = Craft::$app->getUsers()->getUserByUsernameOrEmail($username);
                // Delay randomly between 0 and 500 ms.
                usleep(random_int(0, 500000));

                if (!$user || $user->password === null) {
                    // Delay again to match $user->authenticate()'s delay
                    Craft::$app->getSecurity()->validatePassword('p@ss1w0rd', '$2y$13$nj9aiBeb7RfEfYP3Cum6Revyu14QelGGxwcnFUKXIrQUitSodEPRi');
                    return null;
                }

                return $user->authenticate($password) ? $user : null;
            }
        ];
        return $behaviors;
    }

    public function actionIndex(): string
    {
        $this->requireAdmin();

        $request = Craft::$app->getRequest();
        $response = Craft::$app->getResponse();

        $useGz = $request->getQueryParam('compression', '') === 'gzip';

        $response->format = Response::FORMAT_RAW;
        $headers = $response->getHeaders();
        if ($useGz) {
            $headers->set('Content-Type', 'application/gzip');
        } else {
            $headers->set('Content-Type', 'text/plain');
        }
        return Craftdbextract::$plugin->getDb()->dump($useGz);
    }
}
