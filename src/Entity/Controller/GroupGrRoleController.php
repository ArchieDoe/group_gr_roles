<?php

namespace Drupal\group_gr_roles\Entity\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Entity;
use Drupal\group_gr_roles\Entity\GroupGrRole;

/**
 * Group specific role controller.
 *
 * Class GroupGrRoleController
 * @package Drupal\group_gr_role\Entity\Controller
 */
class GroupGrRoleController extends ControllerBase {

  /**
   * @param \Drupal\core\Entity\Entity $group
   * @return array
   */
  public function listing(Entity $group) {
    return [
      '#markup' => 'mama, ya v televizore.'
    ];
  }


  /**
   * Form constructor for group_gr_role add form.
   *
   * @param \Drupal\Core\Entity\Entity $group
   *   The group to add the group role to.
   *
   * @return array
   *   An associative array containing:
   *   - group_gr_role_form: The group_gr_role form as a renderable array.
   */
  public function add(Entity $group) {
    $build['#title'] = $this->t('Create New Role');

    // Show the actual reply box.
    $group_gr_role = GroupGrRole::create(['group_id' => $group->id(), 'group_type' => $group->bundle()]);
    $build['group_gr_role_form'] = $this->entityFormBuilder()->getForm($group_gr_role, 'add');

    return $build;
  }

  /**
   * Page title for group-specific role add page.
   *
   * @param \Drupal\Core\Entity\Entity $group
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public function addPageTitle(Entity $group) {
    return $this->t('Add role for @gr_name group', ['@gr_name' => $group->getLabel()]);
  }
}