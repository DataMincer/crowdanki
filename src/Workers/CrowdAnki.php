<?php

namespace DataMincerCrowdAnki\Worker;

use DataMincerCore\Plugin\PluginWorkerBase;

class CrowdAnki extends PluginWorkerBase {

  protected static $pluginId = 'crowdanki';

  /**
   * @inheritDoc
   */
  public function process() {
    $data = yield;
    yield $data;
  }

}
