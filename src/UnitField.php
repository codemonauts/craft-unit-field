<?php

namespace codemonauts\unitfield;

use codemonauts\unitfield\fields\Unit;
use codemonauts\unitfield\models\Settings;
use Craft;
use \craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use yii\base\Event;

class UnitField extends Plugin
{
    /**
     * @inheritDoc
     */
    public $hasCpSettings = true;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = Unit::class;
        });
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritDoc
     */
    protected function settingsHtml()
    {
        $settings = $this->getSettings();

        $cols = [
            'group' => [
                'heading' => 'Group?',
                'type' => 'checkbox',
                'toggle' => ['!value'],
            ],
            'label' => [
                'heading' => 'Label*',
                'type' => 'singleline',
            ],
            'value' => [
                'heading' => 'Value*',
                'type' => 'singleline',
            ],
        ];

        $units = $settings->units;

        return Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'editableTableField', [
            [
                'label' => Craft::t('unitfield', 'Units of Measurement'),
                'instructions' => Craft::t('app', 'Define the available units of measurement and their values.'),
                'id' => 'units',
                'name' => 'units',
                'addRowLabel' => Craft::t('unitfield', 'Add an unit'),
                'cols' => $cols,
                'rows' => $units,
            ],
        ]);
    }
}
