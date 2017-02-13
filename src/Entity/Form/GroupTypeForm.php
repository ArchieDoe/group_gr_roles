<?php

namespace Drupal\group_gr_roles\Entity\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Form\GroupTypeForm as GroupGroupTypeForm;

/**
 * Form controller for group type forms.
 */
class GroupTypeForm extends GroupGroupTypeForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $config = \Drupal::config('group_gr_roles.settings');

    $form['allow_group_roles'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow groups to have their own roles and permissions'),
      '#description' => $this->t('Warning! Once set, this configuration cannot be changed!'),
    ];

    $form['roles_inheritance_policy'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create copy of group type roles and permissions on Group creation'),
      '#default_value' => $config->get('roles_inheritance_policy'),
      '#states' => array(
        'visible' => array(
          ':input[name="allow_group_roles"]' => array('checked' => TRUE),
        ),
      ),
    ];

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced'),
      '#description' => $this->t('Warning! Once set, those configurations cannot be changed!'),
      '#open' => FALSE,
    ];

    $form['advanced']['group_role_entity_id'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Group Role Entity Type'),
      '#default_value' => $config->get('group_role_entity_id'),
    ];

    $form['advanced']['membership_plugin_id'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Membership group content plugin ID'),
      '#default_value' => $config->get('membership_plugin_id'),
    ];

    $form['advanced']['membership_loader_id'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Membership Loaded'),
      '#default_value' => $config->get('membership_loader_id'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    if ($form_state->getValue('allow_group_roles')) {
      // That's setting for dummies :D. Will automatically put our module classes
      // to be used by default ().
      $this->entity->set('roles_inheritance_policy', $form_state->getValue('roles_inheritance_policy'));

      // Setting our defaults.
      $this->entity->set('group_role_entity_id', 'group_gr_role');
      $this->entity->set('membership_plugin_id', 'group_gr_membership');
      $this->entity->set('membership_loader_id', 'group_gr_roles.gr_membership_loader');

      // Now we need to check that group type creator didn't provide his own advanced data.
      if ($form['advanced']['group_role_entity_id']['#default_value'] != $form_state->getValue('group_role_entity_id')) {
        $this->entity->set('group_role_entity_id', $form_state->getValue('group_role_entity_id'));
      }

      if ($form['advanced']['membership_plugin_id']['#default_value'] != $form_state->getValue('membership_plugin_id')) {
        $this->entity->set('membership_plugin_id', $form_state->getValue('membership_plugin_id'));
      }

      if ($form['advanced']['membership_loader_id']['#default_value'] != $form_state->getValue('membership_loader_id')) {
        $this->entity->set('membership_loader_id', $form_state->getValue('membership_loader_id'));
      }
    }
    else {
      // Put whatever user provided here. By default it's group configs.
      $this->entity->set('group_role_entity_id', $form_state->getValue('group_role_entity_id'));
      $this->entity->set('membership_plugin_id', $form_state->getValue('membership_plugin_id'));
      $this->entity->set('membership_loader_id', $form_state->getValue('membership_loader_id'));
    }

    parent::save($form, $form_state);
  }

}
