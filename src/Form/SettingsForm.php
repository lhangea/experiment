<?php

/**
 * @file
 * Contains \Drupal\experiment\Form\SettingsForm.
 */

namespace Drupal\experiment\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 * Configure cron settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'experiment_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['experiment.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('experiment.settings');

    $form['use_cookies'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Show each user only one variation.'),
      '#default_value' => $config->get('use_cookies'),
    );

    $form['server_side'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Don\'t use javascript for content retrieval.'),
      '#default_value' => $config->get('server_side'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('experiment.settings')
      ->set('use_cookies', $form_state->getValue('use_cookies'))
      ->set('server_side', $form_state->getValue('server_side'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
