<?php
/**
 * SettingsForm
 * @author Adrien Durmier
 */

namespace Drupal\video\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SettingsForm extends ConfigFormBase {

    protected function getEditableConfigNames(): array {
        return ['maquette.video.settings'];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'maquette.video.settings';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $config = $this->config('maquette.video.settings');

        $form['api_key'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Clé de l&#039;API Google pour Youtube'),
            '#description' => $this->t('Vous pouvez obtenir une clé sur: <a href="https://console.developers.google.com/apis/credentials" target="_blank"><em>https://console.developers.google.com/apis/credentials</em></a>'),
            '#maxlength' => 64,
            '#size' => 64,
            '#weight' => '0',
            '#default_value' => $config->get('maquette.video.api_key'),
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Submit'),
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

        $config = $this->config('maquette.video.settings');
        $config->set('maquette.video.api_key', $form_state->getValue('api_key'));
        $config->save();
        drupal_set_message('La galerie vidéos a bien été mise à jour', 'status');

        parent::submitForm($form, $form_state);

    }

}
