<?php

namespace DataMincerCrowdAnki\Fields;

use DataMincerCore\Plugin\PluginFieldBase;

class Guid extends PluginFieldBase {

  protected static $pluginId = 'guid';

  function getValue($data) {
    return $this->createGuid();
  }

  protected function getGuidChars() {
    return 'abcdefghijklmnopqrstuvwxyz' . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . '0123456789' . "!#$%&()*+,-./:;<=>?@[]^_`{|}~";
  }

  protected function createGuid() {
    $table = $this->getGuidChars();
    $num = rand(0, PHP_INT_MAX);
    $buf = "";
    while ($num) {
      $mod = $num % strlen($table);
      $num = ($num - $mod) / strlen($table);
      $buf = $table[$mod] . $buf;
    }
    return $buf;
  }

}
