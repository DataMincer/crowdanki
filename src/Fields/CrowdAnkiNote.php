<?php

namespace DataMincerCrowdAnki\Fields;

use DataMincerCore\Plugin\PluginFieldBase;
use DataMincerCore\Plugin\PluginFieldInterface;

/**
 * @property PluginFieldInterface[] media
 * @property PluginFieldInterface[] fields
 * @property PluginFieldInterface guid
 * @property PluginFieldInterface tags
 */
class CrowdAnkiNote extends PluginFieldBase {

  protected static $pluginId = 'crowdankinote';
  protected static $pluginType = 'crowdankinote';
  protected static $isDefault = TRUE;

  /**
   * @inheritDoc
   */
  public function getValue($data) {
    $result = $this->evaluateChildren($data);
    // Convert all field values to string
    $result['fields'] = array_map('strval', $result['fields']);
    return $result;
  }


  static function getSchemaChildren() {
    return parent::getSchemaChildren() + [
      # Deck notes and their media
      'media' =>           ['_type' => 'prototype', '_required' => FALSE,
        '_prototype' =>      ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field',
        ]],
      'fields' =>          [ '_type' => 'prototype', '_required' => TRUE,
        '_prototype' =>      ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field',
        ]],
      'guid' =>            ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
      'tags' =>            ['_type' => 'partial', '_required' => FALSE, '_partial' => 'field'],
    ];
  }

}
