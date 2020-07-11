<?php

namespace codemonauts\unitfield\fields;

use codemonauts\unitfield\UnitField;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\helpers\Json;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use yii\db\Schema;

class Unit extends Field implements PreviewableFieldInterface
{
    /**
     * @var mixed Default unit for the field.
     */
    public $defaultUnit;

    /**
     * @var string Macro for rendering table attributes and static content.
     */
    public $template = '';

    /**
     * @var null|array Value / Label assignments
     */
    private $unitLabels;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('unitfield', 'Range of Values with Unit of Measurement');
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_STRING;
    }

    /**
     * @inheritDoc
     */
    public static function valueType(): string
    {
        return 'mixed';
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if ($value === null) {
            return [
                'min' => '',
                'max' => '',
                'unit' => $this->defaultUnit,
                'unitValue' => $this->defaultUnit,
                'unitLabel' => $this->_getUnitLabel($this->defaultUnit),
            ];
        }

        if (is_string($value)) {
            $value = Json::decodeIfJson($value, true);
            $value['unitValue'] = $value['unit'];
            $value['unitLabel'] = $this->_getUnitLabel($value['unit']);
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $options = [];
        $html = '';
        $units = UnitField::getInstance()->getSettings()->units;

        foreach ($units as $unit) {
            if ($unit['group']) {
                $options[] = ['optgroup' => $unit['label']];
            } else {
                $options[] = $unit;
            }
        }

        $html .= Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'selectField', [
            [
                'label' => Craft::t('unitfield', 'Default unit of measurement'),
                'id' => 'defaultUnit',
                'name' => 'defaultUnit',
                'options' => $options,
                'value' => $this->defaultUnit,
            ],
        ]);

        $html .= Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'textField', [
            [
                'label' => Craft::t('unitfield', 'Optional template'),
                'tip' => Craft::t('unitfield', 'Use a Twig template for rendering static representations and table views. Available variables: {{min}}, {{max}}, {{unitValue}} and {{unitLabel}}.'),
                'id' => 'template',
                'name' => 'template',
                'value' => $this->template,
            ],
        ]);

        return $html;
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $setting = UnitField::getInstance()->getSettings();

        return Craft::$app->getView()->renderTemplate('unitfield/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'units' => $setting->units,
                'field' => $this,
            ]);
    }

    /**
     * @inheritdoc
     */
    public function getSearchKeywords($value, ElementInterface $element): string
    {
        return $value['min'] . ' ' . $value['max'] . ' ' . $this->_getUnitLabel($value['unit']);
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        return $this->_renderString($value);
    }

    /**
     * @inheritdoc
     */
    public function getStaticHtml($value, ElementInterface $element): string
    {
        return $this->_renderString($value);
    }

    /**
     * Returns the rendered field values using the Twig template.
     *
     * @param array $value The values to use.
     *
     * @return string
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function _renderString($value): string
    {
        if ($value === null) {
            return '';
        }

        if ($this->template === '') {
            $this->template = '{% if min %}{{ min }} - {% endif %}{{ max }} {{ unitLabel|t("unitfield") }}';
        }

        return Craft::$app->getView()->renderString($this->template, [
            'min' => $value['min'],
            'max' => $value['max'],
            'unitValue' => $value['unitValue'],
            'unitLabel' => $value['unitLabel'],
        ]);
    }

    /**
     * Returns the unit label for a unit value.
     *
     * @param int|string $unitValue The unit value.
     *
     * @return string
     */
    private function _getUnitLabel($unitValue): string
    {
        if ($this->unitLabels === null) {
            $units = UnitField::getInstance()->getSettings()->units;
            $list = [];

            foreach ($units as $unit) {
                if (!$unit['group']) {
                    $list[$unit['value']] = $unit['label'];
                }
            }

            $this->unitLabels = $list;
        }

        return $this->unitLabels[$unitValue] ?? '';
    }
}
