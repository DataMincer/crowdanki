<?php

namespace DataMincerCrowdAnki\Workers;

use DataMincerCore\Plugin\PluginWorkerBase;

class CrowdAnkiDeck extends PluginWorkerBase {

  protected static $pluginId = 'crowdanki';

  /**
   * @inheritDoc
   */
  public function process() {
    $data = yield;
    yield $data;
  }

}
