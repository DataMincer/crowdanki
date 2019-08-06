<?php

namespace DataMincerCrowdAnki\Fields;

use DataMincerCore\Plugin\PluginFieldBase;

/**
 * @property string|null subject
 * @property string|null regexp
 * @property string|null prefix
 * @property string|null suffix
 */
class Rtags extends PluginFieldBase {

  protected static $pluginId = 'rtags';

  function getValue($data) {
    $subject = $this->resolveParam($data, $this->subject);
    $prefix = $this->resolveParam($data, $this->prefix);
    $suffix = $this->resolveParam($data, $this->suffix);
    $result = [];
    if (preg_match_all('~' . $this->regexp . '~', $subject, $matches)) {
      $items = array_unique($matches[0]);
      $result = array_map(function($el) use ($prefix, $suffix) {
        return $prefix . $el . $suffix;
      }, $items);
    }
    return $result;
  }

  static function getSchemaChildren() {
    return parent::getSchemaChildren() + [
        'subject' => [ '_type' => 'text', '_required' => TRUE ],
        'regexp' => [ '_type' => 'text', '_required' => TRUE ],
        'prefix' => [ '_type' => 'text', '_required' => FALSE ],
        'suffix' => [ '_type' => 'text', '_required' => FALSE ],
      ];
  }

}
