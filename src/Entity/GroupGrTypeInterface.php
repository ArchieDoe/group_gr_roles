<?php

namespace Drupal\group_gr_roles\Entity;

/**
 * Extending default group type functionality.
 *
 * Interface GroupGrTypeInterface
 */
interface GroupGrTypeInterface {

  /**
   * Getting role entity ID.
   *
   * @return string
   *   Role entity ID
   */
  public function getRoleEntityId();

  /**
   * Returns membership plugin selected for this group type.
   *
   * @return string
   *   Plugin ID.
   */
  public function getMembershipPluginId();

  /**
   * Returns membership loader selected for this group type.
   *
   * @return string
   *   Membership loaded ID.
   */
  public function getMembershipLoaderId();
}