<?php

namespace Drupal\rest_views\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\rest_views\SerializedData;

/**
 * Plugin implementation of the 'entity_path' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_path",
 *   label = @Translation("Entity path"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class EntityReferencePathFormatter extends EntityReferenceFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode = NULL): array {
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      /** @var \Drupal\Core\Url $url */
      $url = $entity->toUrl();
      $elements[$delta] = [
        '#type' => 'data',
        '#data' => SerializedData::create($url->toString()),
        '#cache' => [
          'tags' => $entity->getCacheTags(),
        ],
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $formState): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    return [];
  }

}
