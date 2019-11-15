<?php

namespace DataMincerCrowdAnki\Workers;

use DataMincerCore\Plugin\PluginWorkerBase;
use DataMincerCrowdAnki\CrowdAnkiApi;
use DataMincerCrowdAnki\Fields\CrowdAnkiNotes;
use Exception;

/**
 * @property CrowdAnkiNotes notes
 */
class CrowdAnkiDecks extends PluginWorkerBase {

  protected static $pluginId = 'crowdankidecks';

  public function evaluate($data = []) {
    return $this->evaluateChildren($data, [], [
      ['build'], ['deck'], ['config'], ['media'], ['model'], ['notes']]);
  }

  /**
   * @inheritDoc
   */
  public function process($config) {
    $data = yield;
    $values = $this->evaluateChildren($data);
    $notes = [];
    $fmedia = [];
    $field_list = [];
    foreach($values['notes'] as $row) {
      if (empty($field_list)) {
        $field_list = array_keys($row['fields']);
      }
      $notes[] = array_intersect_key($row, array_flip(['fields', 'guid', 'tags']));
      if (isset($row['fmedia'])) {
        foreach ($row['fmedia'] as $file) {
          if (!in_array($file, $fmedia)) {
            $fmedia[] = $file;
          }
        }
      }
    }

    $values['model']['fields'] = $field_list;
    $values['notes'] = [
      'data' => $notes,
      'fmedia' => $fmedia,
    ];

    yield $this->mergeResult($values, $data, $config);
  }

  /**
   * @inheritDoc
   */
  public function finalize($config, $results) {
    try {
      foreach($results as $result) {
        CrowdAnkiApi::createDeck($result['row'], $this->_fileManager);
      }
    }
    catch (Exception $e) {
      $this->error($e->getMessage());
    }
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
    return parent::defaultConfig($data) + CrowdAnkiApi::defaultConfig();
  }

}
