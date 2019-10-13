<?php

namespace DataMincerCrowdAnki\Fields;

use DataMincerCore\Plugin\PluginFieldBase;
use DataMincerCore\Plugin\PluginFieldInterface;

/**
 * @property PluginFieldInterface[] fmedia
 * @property PluginFieldInterface[] fields
 * @property PluginFieldInterface note
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
    if (empty($this->note) && empty($this->fields)) {
      $this->error("Either 'note' or 'fields' should be provided.");
    }
    if (!empty($this->note) && !empty($this->fields)) {
      $this->error("Only one key should be used: either 'note' or 'fields', both found.");
    }
    $result = $this->evaluateChildren($data, [['guid'], ['tags']], []);
    $result['fmedia'] = NULL;
    if (!empty($this->fmedia)) {
      $result['fmedia'] = current($this->evaluateChildren($data, [['fmedia']]));
    }
    if (!empty($this->note)) {
      $result['fields'] = $this->note->getValue(['fmedia' => $result['fmedia']] + $data);
    }
    if (!empty($this->fields)) {
      $result['fields'] = current($this->evaluateChildren(['fmedia' => $result['fmedia']] + $data, [['fields']]));
    }
    // Convert all field values to string
    $result['fields'] = array_map('strval', $result['fields']);
    return $result;
  }


  static function getSchemaChildren() {
    return parent::getSchemaChildren() + [
      # Deck notes and their media
      'fmedia' =>           ['_type' => 'prototype', '_required' => FALSE,
        '_prototype' =>      ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field',
        ]],
      'fields' =>          ['_type' => 'prototype', '_required' => FALSE,
        '_prototype' =>      ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field']
      ],
      'note' =>            ['_type' => 'partial', '_required' => FALSE, '_partial' => 'field'],
      'guid' =>            ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
      'tags' =>            ['_type' => 'partial', '_required' => FALSE, '_partial' => 'field'],
    ];
  }

}
