<?php

namespace Drupal\group_gr_roles\Entity;

use Drupal\group\Entity\GroupRoleInterface;

/**
 * Provides an interface defining a group role entity.
 */
interface GroupGrRoleInterface extends GroupRoleInterface {


  /**
   * Returns the ID of the group this role belongs to.
   *
   * @return string
   *   The ID of the group type this role belongs to.
   */
  public function getGroupId();

  /**
   * Checks if role is inherited from parent group type role.
   *
   * @return bool
   *   State of role (inherited or not).
   */
  public function isInherited();

  /**
   * Returns parent role ID.
   *
   * @return integer
   *   Parent role.
   */
  public function getParentRoleId();
}
