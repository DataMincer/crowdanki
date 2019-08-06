<?php

namespace DataMincerCrowdAnki\Fields;

use DataMincerCore\Plugin\PluginFieldBase;

/**
 * @property string rows
 * @property string as
 * @property CrowdAnkiNote each
 */
class CrowdAnkiNotes extends PluginFieldBase {

  protected static $pluginId = 'default';
  protected static $pluginType = 'crowdankinotes';
  protected static $isDefault = TRUE;

  function getValue($data) {
    $rows = $this->resolveParams($data, $this->rows);
    $result = [];
    foreach($rows as $row) {
      $result[] = $this->each->evaluate([$this->as => $row] + $data);
    }
    return $result;
  }

  static function defaultConfig($data = NULL) {
    return parent::defaultConfig($data) + [
      'as' => 'note',
    ];
  }

  static function getSchemaChildren() {
    return parent::getSchemaChildren() + [
      'rows' => ['_type' => 'text', '_required' => TRUE ],
      'as' => ['_type' => 'text', '_required' => TRUE ],
      'each' =>   ['_type' => 'partial', '_required' => TRUE, '_partial' => 'crowdankinote'],
    ];
  }

  static function getSchemaPartials() {
    return [
      'crowdankinote' => [
        '_type' => 'choice', '_required' => TRUE, '_choices' => [
          'one' => [ '_type' => 'array', '_required' => TRUE, '_children' => [
            'crowdankinote' => [ '_type' => 'text',  '_required' => TRUE ],
          ]],
        ],
      ],
    ];
  }


  }
