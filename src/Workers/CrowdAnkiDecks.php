<?php

namespace DataMincerCrowdAnki\Workers;

use DataMincerCore\Exception\PluginException;
use DataMincerCore\Plugin\PluginWorkerBase;
use DataMincerCrowdAnki\CrowdAnkiApi;
use DataMincerCrowdAnki\Fields\CrowdAnkiNotes;

/**
 * @property CrowdAnkiNotes notes
 */
class CrowdAnkiDecks extends PluginWorkerBase {

  protected static $pluginId = 'crowdankidecks';

  public function evaluate($data = []) {
    // Do not evaluate anything, as it's supposed for process
  }

  /**
   * @inheritDoc
   */
  public function process($config) {
    $data = yield;
    $values = $this->evaluateChildren($data);

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

    yield $this->mergeResult($note, $data, $config);
  }

  /**
   * @inheritDoc
   * @throws PluginException
   */
  public function getValue($data) {
    // Copy notes and media data
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
    return parent::defaultConfig($data) + CrowdAnkiApi::defaultConfig($data);
  }

}
