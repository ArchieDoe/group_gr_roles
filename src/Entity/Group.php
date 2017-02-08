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
  public function addMember(UserInterface $account, $values = []) {
    if (!$this->getMember($account)) {
      $this->addContent($account, 'group_gr_membership', $values);
    }
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
