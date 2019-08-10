<?php

namespace DataMincerCrowdAnki\Workers;

use DataMincerCore\Plugin\PluginWorkerBase;
use DataMincerCrowdAnki\CrowdAnkiApi;
use DataMincerCrowdAnki\Fields\CrowdAnkiNote;

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
    $a = 1;
    //CrowdAnkiApi::createDeck($config, );
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
    return parent::defaultConfig($data) + CrowdAnkiApi::defaultConfig($data);
  }
}
