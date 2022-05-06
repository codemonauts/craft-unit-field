<?php

namespace codemonauts\unitfield;

use codemonauts\unitfield\fields\Unit;
use codemonauts\unitfield\models\Settings;
use Craft;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\Cp;
use craft\helpers\UrlHelper;
use craft\services\Fields;
use yii\base\Event;

class UnitField extends Plugin
{
    /**
     * @var \codemonauts\unitfield\UnitField|null
     */
    public static ?UnitField $plugin;

    /**
     * @var \codemonauts\unitfield\models\Settings|null
     */
    public static ?Settings $settings;

    /**
     * @inheritDoc
     */
    public bool $hasCpSettings = true;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        self::$plugin = $this;

        self::$settings = self::$plugin->getSettings();

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = Unit::class;
        });
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }

    /**
     * @inheritDoc
     */
    protected function settingsHtml(): ?string
    {
        $cols = [
            'group' => [
                'heading' => Craft::t('unitfield', 'Group?'),
                'type' => 'checkbox',
                'toggle' => ['!value'],
            ],
            'label' => [
                'heading' => Craft::t('unitfield', 'Label') . '*',
                'type' => 'singleline',
            ],
            'value' => [
                'heading' => Craft::t('unitfield', 'Value') . '*',
                'type' => 'singleline',
            ],
        ];

        return Cp::editableTableFieldHtml([
            'label' => Craft::t('unitfield', 'Units of Measurement'),
            'instructions' => Craft::t('unitfield', 'Define the available units of measurement and their values.'),
            'id' => 'units',
            'name' => 'units',
            'addRowLabel' => Craft::t('unitfield', 'Add an unit'),
            'cols' => $cols,
            'rows' => self::$settings->units,
            'allowAdd' => true,
            'allowReorder' => true,
            'allowDelete' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function afterInstall(): void
    {
        parent::afterInstall();

        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return;
        }

        Craft::$app->getResponse()->redirect(
            UrlHelper::cpUrl('settings/plugins/unitfield')
        )->send();
    }
}
