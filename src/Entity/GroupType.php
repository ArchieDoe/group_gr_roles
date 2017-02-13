<?php

namespace Drupal\group_gr_roles\Entity;

use Drupal\group\Entity\GroupType as GroupGroupType;
use Drupal\Core\Entity\EntityStorageInterface;

class GroupType extends GroupGroupType implements GroupGrTypeInterface {

  /**
   * Role entity type ID we gonna use for storing group roles.
   */
  protected $group_role_entity_id;

  /**
   * Membership plugin ID we gonna use.
   */
  protected $membership_plugin_id;

  /**
   * Membership loaded service ID.
   */
  protected $membership_loader_id;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);

    // If this module was enabled after Group module, that might be a situation
    // when some created group types don't have properties like membership_loader_id
    // stored in database, so taking them from default config.
    $config = \Drupal::config('group_gr_roles.settings');

    if (!$this->group_role_entity_id) {
      $this->group_role_entity_id = $config->get('group_role_entity_id');
    }

    if (!$this->membership_plugin_id) {
      $this->membership_plugin_id = $config->get('membership_plugin_id');
    }

    if (!$this->membership_loader_id) {
      $this->membership_loader_id = $config->get('membership_loader_id');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // We are cool with what default groupType module does, but we want something more :P
    // We installing membership that is provided by administrator and not by default.
    $storage = \Drupal::entityTypeManager()->getStorage('group_content_type');
    $storage->createFromPlugin($this, $this->membership_plugin_id)->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getRoleEntityId() {
    return $this->get('group_role_entity_id');
  }

  /**
   * {@inheritdoc}
   */
  public function getMembershipPluginId() {
    return $this->get('membership_plugin_id');
  }

  /**
   * {@inheritdoc}
   */
  public function getMembershipLoaderId() {
    return $this->get('membership_loader_id');
  }
}
