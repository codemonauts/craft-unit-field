<?php

namespace codemonauts\unitfield\fields;

use codemonauts\unitfield\UnitField;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\helpers\Cp;
use craft\helpers\Json;
use yii\db\Schema;

class Unit extends Field implements PreviewableFieldInterface
{
    /**
     * @var string|int Default unit for the field.
     */
    public mixed $defaultUnit;

    /**
     * @var string Macro for rendering table attributes and static content.
     */
    public string $template = '';

    /**
     * @var null|array Value / Label assignments
     */
    private ?array $unitLabels = null;

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
    public function normalizeValue($value, ElementInterface $element = null): mixed
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
    public function getSettingsHtml(): ?string
    {
        $options = [];
        $units = UnitField::$settings->units;

        foreach ($units as $unit) {
            if ($unit['group']) {
                $options[] = ['optgroup' => $unit['label']];
            } else {
                $options[] = $unit;
            }
        }

        $html = Cp::selectFieldHtml([
            'label' => Craft::t('unitfield', 'Default unit of measurement'),
            'id' => 'defaultUnit',
            'name' => 'defaultUnit',
            'options' => $options,
            'value' => $this->defaultUnit,
        ]);

        $html .= Cp::textFieldHtml([
            'label' => Craft::t('unitfield', 'Optional template'),
            'tip' => Craft::t('unitfield', 'Use a Twig template for rendering static representations and table views. Available variables: {{min}}, {{max}}, {{unitValue}} and {{unitLabel}}.'),
            'id' => 'template',
            'name' => 'template',
            'value' => $this->template,
        ]);

        return $html;
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate('unitfield/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'units' => UnitField::$settings->units,
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
     * @param array|null $value The values to use.
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\SyntaxError
     */
    private function _renderString(?array $value): string
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
    private function _getUnitLabel(mixed $unitValue): string
    {
        if ($this->unitLabels === null) {
            $units = UnitField::$settings->units;
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
