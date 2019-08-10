<?php

namespace DataMincerCrowdAnki\Fields;

use DataMincerCore\Exception\PluginException;
use DataMincerCore\Plugin\PluginFieldBase;
use DataMincerCrowdAnki\CrowdAnkiApi;

/**
 * @property CrowdAnkiNotes notes
 */
class CrowdAnkiDeck extends PluginFieldBase {

  protected static $pluginId = 'crowdankideck';

  /**
   * @inheritDoc
   * @throws PluginException
   */
  public function getValue($data) {
    // Copy notes and media data
    $notes = [];
    $media = [];
    $values = $this->evaluateChildren($data);
    foreach($values['notes'] as $row) {
      $notes[] = array_intersect_key($row, array_flip(['fields', 'guid', 'tags']));
      if (isset($row['media'])) {
        foreach ($row['media'] as $file) {
          if (!in_array($file, $media)) {
            $media[] = $file;
          }
        }
      }
    }
    // Push extra 'fields' branch into $values
    $values = ['fields' => $this->notes->each->fields] + $values;
    CrowdAnkiApi::createDeck($values, $notes, $media);
  }

  static function getSchemaChildren() {
    /** @noinspection DuplicatedCode */
    return parent::getSchemaChildren() + CrowdAnkiApi::schemaChildren() + [
      # Deck notes and their media
      'notes' => ['_type' => 'partial', '_required' => TRUE, '_partial' => 'crowdankinotes'],
    ];
  }

  static function getSchemaPartials() {
    return [
      'crowdankinotes' => [
        '_type' => 'choice', '_required' => TRUE, '_choices' => [
          'one' => [ '_type' => 'array', '_required' => TRUE, '_children' => [
            'crowdankinotes' => [ '_type' => 'text',  '_required' => TRUE ],
          ]],
        ],
      ],
    ];
  }

  static function defaultConfig($data = NULL) {
    return CrowdAnkiApi::defaultConfig($data);
  }

}
