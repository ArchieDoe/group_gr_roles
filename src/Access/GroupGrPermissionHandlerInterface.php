<?php

namespace Drupal\group_gr_roles\Access;

use Drupal\group\Entity\GroupInterface;
use Drupal\group\Access\GroupPermissionHandlerInterface;

/**
 * Defines an interface to list available permissions.
 */
interface GroupGrPermissionHandlerInterface extends GroupPermissionHandlerInterface {

  /**
   * Gets all defined group permissions for a group type.
   *
   * Unlike ::getPermissions(), this also includes the group permissions that
   * were defined by the plugins installed on the group type.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group to retrieve the permission list for.
   *
   * @return array
   *   The full permission list, structured like ::getPermissions().
   *
   * @see \Drupal\group\Access\GroupPermissionHandlerInterface::getPermissions()
   */
  public function getPermissionsByGroup(GroupInterface $group);

}
