<?php

namespace Drupal\group_gr_roles\Entity;

use Drupal\group\Entity\GroupInterface;

/**
 * Provides an interface that has some extra methods required to attach roles to membership.
 */
interface GroupGrInterface extends GroupInterface {

  /**
   * Gets the generic anonymous role ID.
   */
  public function getAnonymousRoleId();

  /**
   * Gets the generic outsider role ID.
   */
  public function getOutsiderRoleId();

  /**
   * Gets the generic member role ID.
   */
  public function getMemberRoleId();
}
