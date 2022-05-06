<?php

namespace codemonauts\unitfield\models;

use craft\base\Model;

class Settings extends Model
{
    /**
     * @var array The supported units od measurement.
     */
    public array $units = [];
}
