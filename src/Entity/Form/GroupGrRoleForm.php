<?php

namespace Drupal\group_gr_roles\Entity\Form;

use Drupal\group\Entity\GroupRole;
use Drupal\group_gr_roles\Entity\GroupGrRole;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Form\GroupRoleForm;

/**
 * Form controller for group role forms.
 */
class GroupGrRoleForm extends GroupRoleForm {
  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\group\Entity\GroupRoleInterface $group_role */
    $group_role = $this->entity;
    $group_role->set('id', $group_role->getGroupTypeId() . '-' . $group_role->getGroupId() . '-' . $group_role->id());
    $group_role->set('label', trim($group_role->label()));

    $status = $group_role->save();
    $t_args = ['%label' => $group_role->label()];

    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('The group role %label has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message(t('The group role %label has been added.', $t_args));

      $context = array_merge($t_args, ['link' => $group_role->toLink($this->t('View'), 'collection')->toString()]);
      $this->logger('group')->notice('Added group role %label.', $context);
    }

    $form_state->setRedirectUrl($group_role->toUrl('collection'));
  }

  /**
   * Checks whether a group role ID exists already.
   *
   * @param string $id
   *
   * @return bool
   *   Whether the ID is taken.
   */
  public function exists($id) {
    /** @var \Drupal\group\Entity\GroupRoleInterface $group_role */
    $group_role = $this->entity;
    return (boolean) GroupGrRole::load($group_role->getGroupTypeId() . '-' . $group_role->getGroupId() . '-' . $group_role->id());
  }

}
