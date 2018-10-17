<?php

/**
 * @file
 * Contains \Drupal\sneak_preview\Form\SneakPreviewConfigForm.
 */

namespace Drupal\sneak_preview\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\node\Entity\NodeType;

class SneakPreviewConfigForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sneak_preview_config_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $settings = \Drupal::config('sneak_preview.settings')->get('sneak_preview_config');
    if ($settings) {
      $settings = unserialize($settings);
    }

    $form = [];
    $all_content_types = NodeType::loadMultiple();
    foreach ($all_content_types as $machine_name => $content_type) {
        $options[$machine_name] = $content_type->label();
    }

    // Get saved values or init empty
    // @FIXME
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/sneak_preview.settings.yml and config/schema/sneak_preview.schema.yml.
    $values = \Drupal::config('sneak_preview.settings')->get('sneak_preview_node_types');

    $form['sneak_preview_node_types'] = [
      '#title' => 'Node types with sneak preview',
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $values,
      '#description' => t('Allow sneak preview for the above node types.')
    ];

    // Buttons
    $form['buttons']['save'] = [
      '#type' => 'submit',
      '#value' => t('Save'),
      '#weight' => 140,
    ];

    $form['buttons']['cancel'] = [
      '#markup' => \Drupal::l(t('Cancel'), \Drupal\Core\Url::fromRoute('system.admin_config')),
      '#weight' => 150,
    ];

    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    // Save the values
    \Drupal::configFactory()->getEditable('sneak_preview.settings')->set('sneak_preview_node_types', $form_state->getValue(['sneak_preview_node_types']))->save();
    drupal_set_message(t('Your settings have been saved'));
    drupal_set_message(t('Remember to <a href="!roles_link">select the roles allowed to see Sneak Previews</a>.', [
      '!roles_link' => \Drupal\Core\Url::fromRoute('user.admin_permissions')
      ]));
  }

}
?>
