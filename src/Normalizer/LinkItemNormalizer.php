<?php

/**
 * @file
 * Contains \Drupal\replication\Normalizer\LinkItemNormalizer.
 */

namespace Drupal\replication\Normalizer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\serialization\Normalizer\NormalizerBase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class LinkItemNormalizer extends NormalizerBase implements DenormalizerInterface {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\link\Plugin\Field\FieldType\LinkItem';

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $attributes = [];
    foreach ($object->getProperties(TRUE) as $name => $field) {
      $attributes[$name] = $this->serializer->normalize($field, $format, $context);
    }

    // Add the 'entity_type_id' and 'target_uuid' values if the uri has the
    // 'entity' scheme. These entities will be used later to denormalize this
    // field and set the uri to the correct entity.
    if (isset($attributes['uri'])) {
      $scheme = parse_url($attributes['uri'], PHP_URL_SCHEME);
      if ($scheme === 'entity') {
        list($entity_type, $entity_id) = explode('/', substr($attributes['uri'], 7), 2);
        $entity_manager = \Drupal::entityTypeManager();
        if ($entity = $entity_manager->getStorage($entity_type)->load($entity_id)) {
          $attributes['entity_type_id'] = $entity_type;
          $attributes['target_uuid'] = $entity->uuid();
        }
      }
    }
    return $attributes;
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = array()) {
    if ($this->hasNewEntity($data)) {
      $entity = $data['uri'];
      // As part of a bulk or replication operation there might be multiple
      // parent entities wanting to auto-create the same reference. So at this
      // point this entity might already be saved, so we look it up by UUID and
      // map it correctly.
      // @see \Drupal\relaxed\BulkDocs\BulkDocs::save()
      if ($entity->isNew()) {
        $uuid = $entity->uuid();
        $uuid_index = \Drupal::service('multiversion.entity_index.uuid');
        if ($uuid && $record = $uuid_index->get($uuid)) {
          /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
          $entity_type_manager = \Drupal::service('entity_type.manager');
          $entity_type_id = $entity->getEntityTypeId();

          // Now we have to decide what revision to use.
          $id_key = $entity_type_manager
            ->getDefinition($entity_type_id)
            ->getKey('id');

          // If the referenced entity is a stub, but a full entity already was
          // created, then load and use that entity instead without saving.
          if ($entity->_rev->is_stub && !$record['is_stub']) {
            $entity = $entity_type_manager
              ->getStorage($entity_type_id)
              ->load($record['entity_id']);
          }
          // If the referenced entity is not a stub then map it with the correct
          // ID from the existing record and save it.
          elseif (!$entity->_rev->is_stub) {
            $entity->{$id_key}->value = $record['entity_id'];
            $entity->enforceIsNew(FALSE);
            $entity->save();
          }
        }
        // Just save the entity if no previous record exists.
        else{
          $entity->save();
        }
      }
      // Set the correct value.
      $data['uri'] = 'entity:' . $entity->getEntityTypeId() . '/' . $entity->id();
    }
    return $data;
  }

  /**
   * Determines whether the item holds an unsaved entity.
   *
   * @param $data
   *
   * @return bool TRUE if the item holds an unsaved entity.
   */
  protected function hasNewEntity($data) {
    return $data['uri'] instanceof ContentEntityInterface && $data['uri']->isNew();
  }

}
