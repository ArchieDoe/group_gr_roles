<?php

namespace Drupal\group_gr_roles\Entity;

/**
 * Provides an interface that has some extra methods required to attach roles to membership.
 */
interface GroupGrInterface {

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
