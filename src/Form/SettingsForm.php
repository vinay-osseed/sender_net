<?php

namespace Drupal\sender_net\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure sender.net settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sender_net_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['sender_net.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['api_access_tokens'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Enter your API access token'),
      '#default_value' => $this->config('sender_net.settings')->get('api_access_tokens'),
      '#description' => $this->t('Get API access tokens from sender.net <a href="https://app.sender.net/settings/tokens" target="_blank">account</a>.'),
      '#required' => TRUE,
    ];

    $form['api_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base URL'),
      '#default_value' => 'https://api.sender.net/v2/',
      '#description' => $this->t('Get Base URL from sender.net <a href="https://api.sender.net/#introduction" target="_blank">docs</a>.'),
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('sender_net.settings')
      ->set('api_access_tokens', $form_state->getValue('api_access_tokens'))
      ->set('api_base_url', $form_state->getValue('api_base_url'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
