<?php

namespace Drupal\group_gr_roles\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserInterface;
use Drupal\group\Entity\GroupContent;
use Drupal\group_gr_roles\Entity\GroupGrRole;

// We want to keep same name for our group.
use Drupal\group\Entity\Group as GroupGroup;


class Group extends GroupGroup implements GroupGrInterface {

  use EntityChangedTrait;

  /**
   * Gets the group membership loader.
   *
   * @return \Drupal\group\GroupMembershipLoaderInterface
   */
  protected function membershipLoader() {
    return \Drupal::service('group_gr_roles.gr_membership_loader');
  }

  /**
   * Gets the group role storage.
   *
   * @return \Drupal\group\Entity\Storage\GroupRoleStorageInterface
   */
  protected function groupRoleStorage() {
    return $this->entityTypeManager()->getStorage('group_gr_role');
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupType() {
    return $this->type->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function addContent(ContentEntityInterface $entity, $plugin_id, $values = []) {
    $plugin = $this->getGroupType()->getContentPlugin($plugin_id);

    // Only add the entity if the provided plugin supports it.
    // @todo Verify bundle as well and throw exceptions?
    if ($entity->getEntityTypeId() == $plugin->getEntityTypeId()) {
      $keys = [
        'type' => $plugin->getContentTypeConfigId(),
        'gid' => $this->id(),
        'entity_id' => $entity->id(),
      ];
      GroupContent::create($keys + $values)->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getContent($plugin_id = NULL, $filters = []) {
    return $this->groupContentStorage()
      ->loadByGroup($this, $plugin_id, $filters);
  }

  /**
   * {@inheritdoc}
   */
  public function getContentByEntityId($plugin_id, $id) {
    return $this->getContent($plugin_id, ['entity_id' => $id]);
  }

  /**
   * {@inheritdoc}
   */
  public function getContentEntities($plugin_id = NULL, $filters = []) {
    $entities = [];

    foreach ($this->getContent($plugin_id, $filters) as $group_content) {
      $entities[] = $group_content->getEntity();
    }

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function addMember(UserInterface $account, $values = []) {
    if (!$this->getMember($account)) {
      $this->addContent($account, 'group_gr_membership', $values);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getMember(AccountInterface $account) {
    return $this->membershipLoader()->load($this, $account);
  }

  /**
   * {@inheritdoc}
   */
  public function getMembers($roles = NULL) {
    return $this->membershipLoader()->loadByGroup($this, $roles);
  }

  /**
   * {@inheritdoc}
   */
  public function hasPermission($permission, AccountInterface $account) {
    // If the account can bypass all group access, return immediately.
    if ($account->hasPermission('bypass group access')) {
      return TRUE;
    }

    // Before anything else, check if the user can administer the group.
    if ($permission != 'administer group' && $this->hasPermission('administer group', $account)) {
      return TRUE;
    }

    // Retrieve all of the group roles the user may get for the group.
    $group_roles = $this->groupRoleStorage()
      ->loadByUserAndGroup($account, $this);

    // Check each retrieved role for the requested permission.
    foreach ($group_roles as $group_role) {
      if ($group_role->hasPermission($permission)) {
        return TRUE;
      }
    }

    // If no role had the requested permission, we deny access.
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Group creator'))
      ->setDescription(t('The username of the group creator.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\group\Entity\Group::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created on'))
      ->setDescription(t('The time that the group was created.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'hidden',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed on'))
      ->setDescription(t('The time that the group was last edited.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'hidden',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('view', TRUE);

    if (\Drupal::moduleHandler()->moduleExists('path')) {
      $fields['path'] = BaseFieldDefinition::create('path')
        ->setLabel(t('URL alias'))
        ->setTranslatable(TRUE)
        ->setDisplayOptions('form', array(
          'type' => 'path',
          'weight' => 30,
        ))
        ->setDisplayConfigurable('form', TRUE)
        ->setCustomStorage(TRUE);
    }

    return $fields;
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // If a new group is created, add the creator as a member by default.
    // @todo Add creator roles by passing in a second parameter like this:
    // ['group_roles' => ['foo', 'bar']].
    if ($update === FALSE) {
      $this->addMember($this->getOwner());
    }

    if (!$update) {
      // Store the id in a short variable for readability.
      $group_type_id = $this->getGroupType()->id();
      $group_id = $this->id();

      // Create the three special roles for the group.
      GroupGrRole::create([
        'id' => $this->getAnonymousRoleId(),
        'label' => t('Anonymous'),
        'weight' => -102,
        'internal' => TRUE,
        'audience' => 'anonymous',
        'group_type' => $group_type_id,
        'group_id' => $group_id,
      ])->save();
      GroupGrRole::create([
        'id' => $this->getOutsiderRoleId(),
        'label' => t('Outsider'),
        'weight' => -101,
        'internal' => TRUE,
        'audience' => 'outsider',
        'group_type' => $group_type_id,
        'group_id' => $group_id,
      ])->save();
      GroupGrRole::create([
        'id' => $this->getMemberRoleId(),
        'label' => t('Member'),
        'weight' => -100,
        'internal' => TRUE,
        'group_type' => $group_type_id,
        'group_id' => $group_id,
      ])->save();
    }
  }

  /**
   * @inheritdoc
   */
  public function getAnonymousRoleId() {
    return $this->getGroupType()->id() . '-' . $this->id() . '-anonymous';
  }

  /**
   * @inheritdoc
   */
  public function getOutsiderRoleId() {
    return $this->getGroupType()->id() . '-' . $this->id() . '-outsider';
  }

  /**
   * @inheritdoc
   */
  public function getMemberRoleId() {
    return $this->getGroupType()->id() . '-' . $this->id() . '-member';
  }
}
