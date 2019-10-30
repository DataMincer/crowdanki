<?php

namespace DataMincerCrowdAnki;

use DataMincerCore\FileManager;
use DataMincerCore\Util;
use DirectoryIterator;
use Exception;
use Generator;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;

class CrowdAnkiApi {

  /**
   * @param $values
   * @param FileManager $file_manager
   * @throws Exception
   */
  public static function createDeck($values, $file_manager) {
    $build = static::buildDeck($values);
    if (!$values['preview']) {
      static::writeDeck($values, $build, $file_manager);
    }
    else {
      static::writePreview($values, $build, $file_manager);
    }
  }

  /**
   * @param $values
   * @param $build
   * @param FileManager $file_manager
   */
  protected static function writeDeck($values, $build, $file_manager) {
    // Generating deck
    $path = $file_manager->resolveUri($values['build']);
    $build_name =  basename($path);
    Util::prepareDir($path);
    file_put_contents($path . '/' . $build_name . '.json', Util::toJson(static::serializeObjects($build), TRUE));
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
   * @return array|mixed
   * @throws Exception
   */
  protected static function buildDeck($values) {
    return [
      '__type__' => 'Deck',
      'crowdanki_uuid' => $values['deck']['uuid'],
      'name' => $values['deck']['name'],
      'desc' => $values['deck']['desc'] ?? '',
      'deck_configurations' => [static::buildDeckConfig($values)],
      'deck_config_uuid' => $values['config']['uuid'],
      'note_models' => [static::buildDeckModel($values)],
      'media_files' => static::buildMedia($values),
      'notes' => static::buildDeckNotes($values),
    ] + $values['defaults']['deck'];
  }

  /**
   * @param $values
   * @return array
   */
  protected static function buildMedia($values) {
    $media = $values['notes']['media'];
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
   * @param $values
   * @return array
   * @throws Exception
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
    foreach ($values['model']['fields'] as $field_name) {
      $result[] = ['name' => $field_name, 'ord' => $i++] + $values['defaults']['model_field'];
    }
    return $result;
  }

  /**
   * @param $values
   * @return array
   * @throws Exception
   */
  protected static function buildDeckTemplates($values) {
    $result = [];
    $i = 0;
    foreach ($values['model']['templates'] as $name => $template) {
      if (($parts = preg_split('~(\r?\n\r?\n--\r?\n\r?\n|<split\s*/>|<split></split>)~', $template)) === FALSE || count($parts) !== 2) {
        throw new Exception("Incorrect template: {$name}. It must consist of two parts divided by '--' on a separate line.");
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
   * @param $values
   * @return array
   */
  protected static function buildDeckNotes($values) {
    $result = [];
    foreach ($values['notes']['data'] as $i => $note) {
      $result[$i] = [
        '__type__' => 'Note',
        'data' => "",
        'fields' => array_values($note['fields']),
        'guid' => $note['guid'],
        'note_model_uuid' => $values['model']['uuid'],
        'tags' => $note['tags'] ?? [],
      ] + $values['defaults']['note'];
    }
    return $result;
  }

  /**
   * @param $values
   * @param $build
   * @param FileManager $file_manager
   * @throws Exception
   */
  protected static function writePreview($values, $build, $file_manager) {
    // Previewing cards
    $path = $file_manager->resolveUri($values['build']);
    $build_name =  basename($path);
    $model = current($build['note_models']);
    $styles_path = $path . '/media';
    Util::prepareDir($styles_path);
    file_put_contents($styles_path . '/styles.css', $model['css']);
    $path .= '.preview';
    Util::prepareDir($path);
    // Get data for the preview
    if (is_array($values['preview'])) {
      $note_offset = $values['preview']['note'];
    }
    else {
      $note_offset = 0;
    }
    $note_data = [];
    foreach($model['flds'] as $field_id => $field_info) {
      $note_data[$field_info['name']] = $build['notes'][$note_offset]['fields'][$field_id];
    }
    if (count($model['tmpls']) > 0) {
      foreach ($model['tmpls'] as $tmpl_index => $tmpl) {
        static::writePreviews($note_data, $tmpl, $build_name, $path, $tmpl_index);
      }
    }
    else {
      $tmpl = current($model['tmpls']);
      static::writePreviews($note_data, $tmpl, $build_name, $path);
    }
  }

  /**
   * @param $note_data
   * @param $tmpl
   * @param $build_name
   * @param $path
   * @param string $postfix
   * @throws Exception
   */
  protected static function writePreviews($note_data, $tmpl, $build_name, $path, $postfix = '') {
    $sides = ['qfmt' => ['front', 'back'], 'afmt' => ['back', 'front']];
    foreach ($sides as $side_index => $side_info) {
      $context = [
        'card' => $tmpl[$side_index],
        'buildName' => $build_name,
        'side' => $side_info[0],
        'other_side' => $side_info[1],
      ];
      foreach (static::getDefaultCardTemplates($context) as $anki_card_info) {
        $prefix = key($anki_card_info);
        $contents = static::applyTestData(current($anki_card_info), $note_data);
        $filename = $prefix . '-' . $side_info[0] . ($postfix ? '-' . $postfix : '') . '.html';
        // TODO: Apply test data
        file_put_contents($path . '/' . $filename, $contents);
      }
    }
  }

  /**
   * @param $context
   * @return Generator
   * @throws Exception
   */
  protected static function getDefaultCardTemplates($context) {
    $template_files = [];
    $templates_path = __DIR__ . '/preview';
    foreach (new DirectoryIterator($templates_path) as $fileInfo) {
      if ($fileInfo->isDot()) continue;
      if (preg_match('~(.+?)\.html\.twig~', $fileInfo->getFilename(), $matches)) {
        $template_files[$matches[1]] = $matches[0];
      }
    }
    $twig = new Environment(new FilesystemLoader($templates_path));
    foreach ($template_files as $name => $template_file) {
      try {
        $template = $twig->load($template_file);
        $result = $template->render($context + [
          'template' => $name,
          'templates' => array_keys($template_files),
        ]);
        yield [$name => $result];
      }
      catch (LoaderError | RuntimeError | SyntaxError $e) {
        throw new Exception($e->getMessage() . "\nTemplate: " . $template_file);
      }
    }
  }

  /**
   * @param $card
   * @param $data
   * @return string
   * @throws Exception
   */
  protected static function applyTestData($card, $data) {
    return static::renderTemplate($card, $data);
//    // TODO: Emulate Anki template engine, meanwhile using Twig LOL
//    $twig = new Environment(new ArrayLoader());
//    /** @noinspection PhpUndefinedMethodInspection */
//    $twig->getLoader()->setTemplate('template', $card);
//    try {
//      $result = $twig->load('template')->render($data);
//    }
//    catch (LoaderError | RuntimeError | SyntaxError $e) {
//      throw new Exception($e->getMessage() . "\nTemplate:\n" . $card);
//    }
//    return $result;
  }

  protected static function renderTemplate($template, $data) {
    // TODO: Emulate Anki template engine, meanwhile using Twig LOL
    // Replace fields
    $res = $template;
    $res = preg_replace_callback('~{{\s*([^\s]+)\s*}}~', function ($match) use ($data) {
      $field = $match[1];
      if (array_key_exists($field, $data)) {
        return $data[$field];
      }
      return '';
    }, $res);
    // Replace [sound:...] and [img:...] tags
    /** @noinspection RegExpRedundantEscape */
    $res = preg_replace_callback('~\[(sound|img):([^\]]+)\]~', function ($match) use ($data) {
      $type = $match[1];
      $src = $match[2];
      switch ($type) {
        case 'sound':
          $res = "<audio src='$src' autoplay/>";
          break;
        case 'img':
        default:
          $res = "<img src='$src'/>";
          break;
      }
      return $res;
    }, $res);
    return $res;
  }

  static function schemaChildren() {
    return [
      'build' =>      ['_type' => 'partial', '_required' => TRUE, '_partial' => 'field'],
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

      'preview' =>      ['_type' => 'choice', '_required' => FALSE, '_choices' => [
        'bool' =>         ['_type' => 'boolean', '_required' => TRUE],
        'note' =>         ['_type' => 'array', '_required' => FALSE, '_children' => [
          'note' =>         ['_type' => 'number', '_required' => FALSE]
        ]]
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

  static function defaultConfig() {
    return [
      'preview' => FALSE,
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
