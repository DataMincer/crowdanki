<?php

namespace DataMincerCrowdAnki\Workers;

use DataMincerCore\Plugin\PluginWorkerBase;
use DataMincerCrowdAnki\CrowdAnkiApi;
use DataMincerCrowdAnki\Fields\CrowdAnkiNote;
use Exception;

/**
 * @property CrowdAnkiNote note
 */
class CrowdAnkiDeck extends PluginWorkerBase {

  protected static $pluginId = 'crowdankideck';

  protected $globals = [];
  protected $notes = [];

  public function evaluate($data = []) {
    // Do not evaluate 'note' field, as it's intended for process()
    return $this->evaluateChildren($data, [], [['note']]);
  }

  /**
   * @inheritDoc
   */
  public function process($config) {
    $data = yield;
    $note = $this->note->evaluate($data);
    yield $this->mergeResult($note, $data, $config);
  }

  public function finalize($config, $results) {
    $notes = [];
    $media = [];
    foreach($results as $result) {
      $notes[] = array_intersect_key($result['row'], array_flip(['fields', 'guid', 'tags']));
      if (isset($result['row']['media'])) {
        foreach ($result['row']['media'] as $file) {
          if (!in_array($file, $media)) {
            $media[] = $file;
          }
        }
      }
    }
    $values = $config;
    $values['model']['fields'] = array_keys($this->note->fields);
    $values['notes'] = [
      'data' => $notes,
      'media' => $media,
    ];
    try {
      CrowdAnkiApi::createDeck($values);
    }
    catch (Exception $e) {
      $this->error($e->getMessage());
    }
  }


  static function getSchemaChildren() {
    /** @noinspection DuplicatedCode */
    return parent::getSchemaChildren() + CrowdAnkiApi::schemaChildren() + [
        # Deck note and their media
        'note' => ['_type' => 'partial', '_required' => TRUE, '_partial' => 'crowdankinote'],
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

  static function defaultConfig($data = NULL) {
    return parent::defaultConfig($data) + CrowdAnkiApi::defaultConfig();
  }
}
