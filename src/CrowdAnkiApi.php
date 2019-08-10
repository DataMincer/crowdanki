<?php

namespace DataMincerCrowdAnki;

use DataMincerCore\Exception\PluginException;
use DataMincerCore\Util;

class CrowdAnkiApi {

  /**
   * @param $values
   * @param $notes
   * @param $media
   * @throws PluginException
   */
  public static function createDeck($values, $notes, $media) {
    $build = static::buildDeck($values, $notes, $media);
    $path = $values['build']['path'] . '/' . $values['build']['name'] . '/' . $values['build']['name'] . '.json';
    Util::prepareDir(dirname($path));
    file_put_contents($path, Util::toJson(static::serializeObjects($build), TRUE));
  }

  protected static function serializeObjects($data) {
    $result = [];
    foreach ($data as $key => $info) {
      if (is_array($info)) {
        $result[$key] = static::serializeObjects($info);
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
  protected static function buildDeck($values, $notes, $media) {
    return [
      '__type__' => 'Deck',
      'crowdanki_uuid' => $values['deck']['uuid'],
      'name' => $values['deck']['name'],
      'desc' => $values['deck']['desc'] ?? '',
      'deck_configurations' => [static::buildDeckConfig($values)],
      'deck_config_uuid' => $values['config']['uuid'],
      'note_models' => [static::buildDeckModel($values)],
      'media_files' => static::buildMedia($values, $media),
      'notes' => static::buildDeckNotes($values['config']['uuid'], $values, $notes),
    ] + $values['defaults']['deck'];
  }

  /**
   * @param $values
   * @param $media
   * @return array
   */
  protected static function buildMedia($values, $media) {
    if (isset($values['media'])) {
      foreach ($values['media'] as $list) {
        foreach ($list as $file) {
          if (!in_array($file, $media)) {
            $media[] = $file;
          }
        }
      }
    }
    return $media;
  }

  /**
   * @param $values
   * @return array|mixed
   */
  protected static function buildDeckConfig($values) {
    return [
      '__type__' => 'DeckConfig',
      'crowdanki_uuid' => $values['config']['uuid'],
      'name' => $values['config']['name'],
    ] + $values['defaults']['config'];
  }

  /**
   * @param $uuid
   * @param $values
   * @return array
   * @throws PluginException
   */
  protected static function buildDeckModel($values) {
    return [
      '__type__' => 'NoteModel',
      'crowdanki_uuid' => $values['model']['uuid'],
      'name' => $values['model']['name'],
      'flds' => static::buildDeckFields($values),
      'tmpls' => static::buildDeckTemplates($values),
      'css' => $values['model']['css'],
    ] + $values['defaults']['model'];
  }

  /**
   * @param $values
   * @return array
   */
  protected static function buildDeckFields($values) {
    $result = [];
    $i = 0;
    foreach (array_keys($values['fields']) as $field_name) {
      $result[] = ['name' => $field_name, 'ord' => $i++] + $values['defaults']['model_field'];
    }
    return $result;
  }

  /**
   * @param $values
   * @return array
   * @throws PluginException
   */
  protected static function buildDeckTemplates($values) {
    $result = [];
    $i = 0;
    foreach ($values['model']['templates'] as $name => $template) {
      if (($parts = preg_split('~\r?\n\r?\n--\r?\n\r?\n~', $template)) === FALSE) {
        throw new PluginException("Incorrect template: {$name}. It must consist of two parts divided by '--' on a separate line.", NULL);
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
  protected static function buildDeckNotes($model_uuid, $values, $notes) {
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

  static function schemaChildren() {
    return [
      # Build params
      'build' =>      ['_type' => 'array', '_required' => TRUE, '_children' => [
        'name' =>       ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
        'path' =>       ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
      ]],
      'deck' =>       ['_type' => 'array', '_required' => TRUE, '_children' => [
        'uuid' =>       ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
        'name' =>       ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
        'desc' =>       ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
      ]],
      'config' =>     ['_type' => 'array', '_required' => TRUE, '_children' => [
        'uuid' =>       ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
        'name' =>       ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
      ]],
      'model' =>      ['_type' => 'array', '_required' => TRUE, '_children' => [
        'uuid' =>       ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
        'name' =>       ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
        'templates' =>  ['_type' => 'prototype', '_required' => FALSE,
          '_prototype' =>  ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field',
        ]],
        'css' =>        ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
      ]],
      'media' =>      ['_type' => 'prototype', '_required' => FALSE,
        '_prototype' =>      ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field',
      ]],

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
