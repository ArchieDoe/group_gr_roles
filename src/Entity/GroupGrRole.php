<?php

namespace Drupal\group_gr_roles\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\group\Entity\GroupRole;
use Drupal\group_gr_roles\Entity\GroupGrRoleInterface;

/**
 * Defines the Group role configuration entity.
 *
 * @ConfigEntityType(
 *   id = "group_gr_role",
 *   label = @Translation("Group specific role"),
 *   label_singular = @Translation("group specific role"),
 *   label_plural = @Translation("group specific roles"),
 *   label_count = @PluralTranslation(
 *     singular = "@count group specific role",
 *     plural = "@count group specific roles"
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\group_gr_roles\Entity\Storage\GroupGrRoleStorage",
 *     "access" = "Drupal\group\Entity\Access\GroupRoleAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\group_gr_roles\Entity\Form\GroupGrRoleForm",
 *       "edit" = "Drupal\group_gr_roles\Entity\Form\GroupGrRoleForm",
 *       "delete" = "Drupal\group\Entity\Form\GroupRoleDeleteForm"
 *     },
 *     "list_builder" = "Drupal\group_gr_roles\Entity\Controller\GroupGrRoleListBuilder",
 *   },
 *   admin_permission = "administer group",
 *   config_prefix = "gr_role",
 *   static_cache = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "weight" = "weight",
 *     "label" = "label"
 *   },
 *   links = {
 *     "collection" = "/group/{group}/roles",
 *     "edit-form" = "/group/{group}/roles/manage/{group_gr_role}",
 *     "delete-form" = "/group/{group}/roles/delete/{group_gr_role}",
 *     "permissions-form" = "/group/{group}/roles/permissions/{group_gr_role}"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "weight",
 *     "internal",
 *     "audience",
 *     "group_type",
 *     "group_id",
 *     "permissions_ui",
 *     "permissions"
 *   }
 * )
 */
class GroupGrRole extends GroupRole implements GroupGrRoleInterface {

  /**
   * The ID of the group this role belongs to.
   *
   * @var string
   */
  protected $group_id;

  /**
   * {@inheritdoc}
   */
  public function getGroupId() {
    return $this->group_id;
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);
    $uri_route_parameters['group'] = $this->getGroupId();
    $uri_route_parameters['group_gr_role'] = $this->id();
    return $uri_route_parameters;
  }

}
