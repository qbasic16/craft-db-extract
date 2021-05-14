<?php

/**
 * craft-db-extract plugin for Craft CMS 3.x
 *
 * A small helper Plugin for CraftCMS to download the DB over HTTP requiring authorization.
 *
 * @link      https://github.com/qbasic16/
 * @copyright Copyright (c) 2020 P. Janser
 */

namespace pjanser\craftdbextract;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use pjanser\craftdbextract\services\DbService;
use yii\base\Event;

/**
 * Class Craftdbextract
 *
 * @author    P. Janser
 * @package   Craftdbextract
 * @since     1.0.0-alpha.1
 *
 */
class Craftdbextract extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Craftdbextract
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public $hasCpSettings = false;

    /**
     * @var bool
     */
    public $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->_registerServices();

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );
    }

    public function getDb(): DbService
    {
        return $this->get('db');
    }

    // Protected Methods
    // =========================================================================

    // Private Methods
    // =========================================================================

    private function _registerServices(): void
    {
        $this->setComponents([
            'db' => DbService::class
        ]);
    }
}
