<?php

namespace DataMincerCrowdAnki\Fields;

use DataMincerCore\Plugin\PluginFieldBase;
use DataMincerCore\Plugin\PluginFieldInterface;

/**
 * @property PluginFieldInterface source
 * @property string as
 * @property CrowdAnkiNote each
 */
class CrowdAnkiNotes extends PluginFieldBase {

  protected static $pluginId = 'crowdankinotes';
  protected static $pluginType = 'crowdankinotes';
  protected static $isDefault = TRUE;

  function getValue($data) {
    $source = $this->source->getValue($data);
    $result = [];
    foreach($source as $item) {
      $result[] = $this->each->evaluate([$this->as => $item] + $data);
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
      'source' => [ '_type' => 'partial', '_required' => TRUE, '_partial' => 'field' ],
      'as' => ['_type' => 'text', '_required' => FALSE ],
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
