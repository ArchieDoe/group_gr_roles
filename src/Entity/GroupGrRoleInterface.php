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

}
