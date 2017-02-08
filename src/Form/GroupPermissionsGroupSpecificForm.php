<?php

namespace Drupal\group_gr_roles\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Access\GroupPermissionHandlerInterface;
use Drupal\group\Entity\Group;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\group\Form\GroupPermissionsForm;

/**
 * Provides the user permissions administration form for a specific group type.
 */
class GroupPermissionsGroupSpecificForm extends GroupPermissionsForm {

  /**
   * The specific group role for this form.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new GroupPermissionsForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\group\Access\GroupPermissionHandlerInterface $permission_handler
   *   The group permission handler.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, GroupPermissionHandlerInterface $permission_handler, ModuleHandlerInterface $module_handler) {
    parent::__construct($permission_handler, $module_handler);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('group_gr_roles.permissions'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getGroupType() {
    return $this->group->bundle();
  }


  /**
   * Get current group.
   */
  protected function getGroup() {
    return $this->group;
  }

  /**
   * {@inheritdoc}
   */
  protected function getGroupRoles() {
    $properties = [
      'group_id' => $this->group->id(),
      'permissions_ui' => TRUE,
    ];

    return $this->entityTypeManager
      ->getStorage('group_gr_role')
      ->loadByProperties($properties);
  }

  /**
   * {@inheritdoc}
   */
  protected function getPermissions() {
    $permissions_by_provider = [];

    // Create a list of group permissions ordered by their provider.
    foreach ($this->groupPermissionHandler->getPermissionsByGroup($this->getGroup()) as $permission_name => $permission) {
      $permissions_by_provider[$permission['provider']][$permission_name] = $permission;
    }

    return $permissions_by_provider;
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string $group
   *   The group ID used for this form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $group = NULL) {
    // Loading group.
    $this->group = Group::load($group);
    return parent::buildForm($form, $form_state);
  }

}
