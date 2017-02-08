<?php

namespace Drupal\group_gr_roles\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;

/**
 * Only shows the group roles which are available for a group type.
 *
 * The only handler setting is 'group_type_id', a required string that points
 * to the ID of the group type for which this handler will be run.
 *
 * @EntityReferenceSelection(
 *   id = "group_type:group_gr_role",
 *   label = @Translation("Group type gr role selection"),
 *   entity_types = {"group_gr_role"},
 *   group = "group_type",
 *   weight = 0
 * )
 */
class GroupTypeGrRoleSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $route_match = \Drupal::routeMatch();
    $group = $route_match->getParameter('group');

    $query = parent::buildEntityQuery($match, $match_operator);
    $query->condition('group_id', $group->id(), '=');
    $query->condition('internal', 0, '=');

    return $query;
  }

}
