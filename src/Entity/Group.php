<?php

namespace Drupal\group_gr_roles\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\user\UserInterface;

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
    $membership_loaded = $this->getGroupType()->getMembershipLoaderId();

    return \Drupal::service($membership_loaded);
  }

  /**
   * Gets the group role storage.
   *
   * @return \Drupal\group\Entity\Storage\GroupRoleStorageInterface
   */
  protected function groupRoleStorage() {
    $role_entity = $this->getGroupType()->getRoleEntityId();

    return $this->entityTypeManager()->getStorage($role_entity);
  }

  /**
   * {@inheritdoc}
   */
  public function addMember(UserInterface $account, $values = []) {
    $membership = $this->getGroupType()->getMembershipPluginId();

    if (!$this->getMember($account)) {
      $this->addContent($account, $membership, $values);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    if (!$update) {
      // Store the id in a short variable for readability.
      $group_type = $this->getGroupType();
      $group_id = $this->id();



      // Create the three special roles for the group.
      $new_anonymous = [
        'id' => $this->getAnonymousRoleId(),
        'label' => t('Anonymous'),
        'weight' => -102,
        'internal' => TRUE,
        'audience' => 'anonymous',
        'group_type' => $group_type->id(),
        'group_id' => $group_id,
      ];

      $new_outsider = [
        'id' => $this->getOutsiderRoleId(),
        'label' => t('Outsider'),
        'weight' => -101,
        'internal' => TRUE,
        'audience' => 'outsider',
        'group_type' => $group_type->id(),
        'group_id' => $group_id,
      ];

      $new_member = [
        'id' => $this->getMemberRoleId(),
        'label' => t('Member'),
        'weight' => -100,
        'internal' => TRUE,
        'group_type' => $group_type->id(),
        'group_id' => $group_id,
      ];

      // Adding permissions for 3 special roles if we need to.
      if ($this->getGroupType()->get('roles_inheritance_policy')) {
        $roles = $this->getGroupType()->getRoles();

        $new_anonymous['permissions'] = $roles[$group_type->getAnonymousRoleId()]->getPermissions();
        $new_outsider['permissions'] = $roles[$group_type->getOutsiderRoleId()]->getPermissions();
        $new_member['permissions'] = $roles[$group_type->getMemberRoleId()]->getPermissions();
      }

      GroupGrRole::create($new_anonymous)->save();
      GroupGrRole::create($new_outsider)->save();
      GroupGrRole::create($new_member)->save();

      // Creating new roles based on parent roles if we have this setting enabled.
      if ($this->getGroupType()->get('roles_inheritance_policy')) {
        $this->inheritCustomRoles();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAnonymousRoleId() {
    return $this->getGroupType()->id() . '-' . $this->id() . '-anonymous';
  }

  /**
   * {@inheritdoc}
   */
  public function getOutsiderRoleId() {
    return $this->getGroupType()->id() . '-' . $this->id() . '-outsider';
  }

  /**
   * {@inheritdoc}
   */
  public function getMemberRoleId() {
    return $this->getGroupType()->id() . '-' . $this->id() . '-member';
  }

  public function inheritCustomRoles() {
    $roles = $this->getGroupType()->getRoles();

    if (!$roles) {
      return;
    }

    $group_type_id = $this->getGroupType()->id();
    $group_id = $this->id();

    foreach ($roles as $role) {
      if (!$role->isInternal()) {
        $parsed_id = explode('-', $role->id());
        $new_id = $group_type_id . '-' . $group_id . '-' . end($parsed_id);

        GroupGrRole::create([
          'id' => $new_id,
          'label' => $role->label(),
          'weight' => $role->getWeight(),
          'internal' => FALSE,
          'group_type' => $group_type_id,
          'group_id' => $group_id,
          'permissions' => $role->getPermissions(),
        ])->save();
      }
    }
  }
}
