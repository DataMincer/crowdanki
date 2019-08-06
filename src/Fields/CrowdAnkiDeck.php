<?php

namespace DataMincerCrowdAnki\Fields;

use DataMincerCore\Exception\PluginException;
use DataMincerCore\Plugin\PluginFieldBase;
use DataMincerCore\Util;

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
    $this->createDeck($values, $notes, $media);
  }

  /**
   * @param $values
   * @param $notes
   * @param $media
   * @throws PluginException
   */
  protected function createDeck($values, $notes, $media) {
    $build = $this->buildDeck($values, $notes, $media);
    $path = $values['build_dir'] . '/' . $values['build_name'] . '/' . $values['build_name'] . '.json';
    Util::prepareDir(dirname($path));
    file_put_contents($path, Util::toJson($this->serializeObjects($build), TRUE));
  }

  protected function serializeObjects($data) {
    $result = [];
    foreach ($data as $key => $info) {
      if (is_array($info)) {
        $result[$key] = $this->serializeObjects($info);
      }
      if (is_object($info)) {
        $result[$key] = strval($info);
      }
      else {
        $result[$key] = $info;
      }
    }
    return $result;
  }

  /**
   * @param $values
   * @param $notes
   * @param $media
   * @return array|mixed
   * @throws PluginException
   */
  protected function buildDeck($values, $notes, $media) {
    return [
      '__type__' => 'Deck',
      'crowdanki_uuid' => $values['deck_uuid'],
      'name' => $values['deck_name'],
      'desc' => $values['deck_desc'] ?? '',
      'deck_configurations' => [$this->buildDeckConfig($config_uuid, $values)],
      'deck_config_uuid' => $config_uuid,
      'note_models' => [$this->buildDeckModel($model_uuid, $values)],
      'media_files' => $this->buildMedia($values, $media),
      'notes' => $this->buildDeckNotes($model_uuid, $values, $notes),
    ] + $values['defaults']['deck'];
  }

  /**
   * @param $values
   * @param $media
   * @return array
   */
  protected function buildMedia($values, $media) {
    if (isset($values['media_files'])) {
      foreach ($values['media_files'] as $list) {
        if (is_array($list)) {
          foreach ($list as $file) {
            if (!in_array($file, $media)) {
              $media[] = $file;
            }
          }
        }
        else {
          $file = $list;
          if (!in_array($file, $media)) {
            $media[] = $file;
          }
        }
      }
    }
    return $media;
  }

  /**
   * @param $uuid
   * @param $values
   * @return array|mixed
   */
  protected function buildDeckConfig(&$uuid, $values) {
    return [
      '__type__' => 'DeckConfig',
      'crowdanki_uuid' => $uuid = $values['config_uuid'],
      'name' => $values['config_name'],
    ] + $values['defaults']['config'];
  }

  /**
   * @param $uuid
   * @param $values
   * @return array
   * @throws PluginException
   */
  protected function buildDeckModel(&$uuid, $values) {
    $a = 1;
    return [
      '__type__' => 'NoteModel',
      'crowdanki_uuid' => $uuid = $values['model_uuid'],
      'name' => $values['model_name'],
      'flds' => $this->buildDeckFields($values),
      'tmpls' => $this->buildDeckTemplates($values),
      'css' => $values['model_css'],
    ] + $values['defaults']['model'];
  }

  /**
   * @param $values
   * @return array
   */
  protected function buildDeckFields($values) {
    $result = [];
    $i = 0;
    foreach (array_keys($this->notes->each->fields) as $field_name) {
      $result[] = ['name' => $field_name, 'ord' => $i++] + $values['defaults']['model_field'];
    }
    return $result;
  }

  /**
   * @param $values
   * @return array
   * @throws PluginException
   */
  protected function buildDeckTemplates($values) {
    $result = [];
    $i = 0;
    foreach ($values['model_templates'] as $name => $template) {
      if (($parts = preg_split('~\r?\n\r?\n--\r?\n\r?\n~', $template)) === FALSE) {
        $this->error("Incorrect template: {$name}. It must consist of two parts divided by '--' on a separate line.");
      }
      $result[] = [
        'name' => $name,
        'qfmt' => $parts[0],
        'afmt' => $parts[1],
        'ord' => $i++,
      ] + $values['defaults']['model_template'];
    }
    return $result;
  }

  /**
   * @param $model_uuid
   * @param $values
   * @param $notes
   * @return array
   */
  protected function buildDeckNotes($model_uuid, $values, $notes) {
    $result = [];
    foreach ($notes as $i => $note) {
      $result[$i] = [
        '__type__' => 'Note',
        'data' => "",
        'fields' => array_values($note['fields']),
        'guid' => $note['guid'],
        'note_model_uuid' => $model_uuid,
        'tags' => $note['tags'] ?? [],
      ] + $values['defaults']['note'];
    }
    return $result;
  }

  static function getSchemaChildren() {
    /** @noinspection DuplicatedCode */
    return parent::getSchemaChildren() + [
      # Build params
      'build_dir' =>       ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
      'build_name' =>  ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],

      # Deck properties
      'deck_uuid' =>       ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
      'deck_name' =>       ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
      'deck_desc' =>       ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],

      # Deck Configuration properties (we support only ONE configuration per deck)
      'config_uuid' =>     ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
      'config_name' =>     ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],

      # Deck model properties (we support only ONE model per deck)
      'model_uuid' =>      ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
      'model_name' =>      ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
      'model_templates' => ['_type' => 'prototype', '_required' => FALSE,
        '_prototype' =>      ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field',
       ]],
      'model_css' =>       ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],

      # Deck media
      'media_files' =>     ['_type' => 'prototype', '_required' => FALSE,
        '_prototype' =>      ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field',
        ]],

      # Deck notes and their media
      'notes' =>            ['_type' => 'partial', '_required' => TRUE, '_partial' => 'crowdankinotes'],

      # Deck defaults
      'defaults' =>        ['_type' => 'array', '_required' => FALSE, '_children' => [
        'deck' =>            ['_type' => 'array', '_required' => FALSE, '_ignore_extra_keys' => TRUE, '_children' => []],
        'config' =>          ['_type' => 'array', '_required' => FALSE, '_ignore_extra_keys' => TRUE, '_children' => []],
        'model' =>           ['_type' => 'array', '_required' => FALSE, '_ignore_extra_keys' => TRUE, '_children' => []],
        'model_field' =>     ['_type' => 'array', '_required' => FALSE, '_ignore_extra_keys' => TRUE, '_children' => []],
        'model_template' =>  ['_type' => 'array', '_required' => FALSE, '_ignore_extra_keys' => TRUE, '_children' => []],
        'note' =>            ['_type' => 'array', '_required' => FALSE, '_ignore_extra_keys' => TRUE, '_children' => []],
      ]],
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
    return [
      'defaults' => [
        'deck' => [
          'dyn' => 0,
          'extendNew' => 10,
          'extendRev' => 50,
          'children' => [],
        ],
        'config' => [
          'autoplay' => true,
          'dyn' => false,
          'lapse' => [
            'delays' => [
              10,
            ],
            'leechAction' => 0,
            'leechFails' => 8,
            'minInt' => 1,
            'mult' => 0.0,
          ],
          'maxTaken' => 60,
          'new' => [
            'bury' => false,
            'delays' => [
              1,
              10,
            ],
            'initialFactor' => 2500,
            'ints' => [
              1,
              4,
              7,
            ],
            'order' => 1,
            'perDay' => 20,
            'separate' => true,
          ],
          'replayq' => true,
          'rev' => [
            'bury' => false,
            'ease4' => 1.3,
            'fuzz' => 0.05,
            'hardFactor' => 1.2,
            'ivlFct' => 1.0,
            'maxIvl' => 36500,
            'minSpace' => 1,
            'perDay' => 200,
          ],
          'timer' => 0,
        ],
        'model' => [
          'latexPost' => "\\end{document}",
          'latexPre' => "\\documentclass[12pt]{article}\n\\special{papersize=3in,5in}\n\\usepackage{amssymb,amsmath}\n\\pagestyle{empty}\n\\setlength{\\parindent}{0in}\n\\begin{document}\n",
          'type' => 0,
          'vers' => [],
          'sortf' => 0,
          "req" => [],
          'tags' => [],
        ],
        'model_field' => [
          'font' => 'Arial',
          'media' => [],
          'rtl' => FALSE,
          'size' => 20,
          'sticky' => FALSE,
        ],
        'model_template' => [
          'qfmt' => '',
          'afmt' => '{{FrontSide}}',
          'bafmt' => '',
          'bqfmt' => '',
          'did' => NULL,
        ],
        'note' => [
          'data' => "",
          'flags' => 0,
          'tags' => [],
        ],
      ],
    ];
  }

}
