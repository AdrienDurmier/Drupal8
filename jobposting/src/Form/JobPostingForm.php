<?php

namespace Drupal\jobposting\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Implements the JobPostingForm controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class JobPostingForm extends FormBase {

    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['title'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Title'),
        ];

        $form['description'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Description'),
        ];

        $form['skills'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Skills'),
        ];

        $form['exprequirements'] = [
            '#type' => 'number',
            '#title' => $this->t('Minimum experience requirements'),
            '#min' => 0,
            '#max' => 40,
            '#description' => $this->t('Years'),
        ];

        $form['employmenttype'] = [
            '#type' => 'checkboxes',
            '#title' => $this->t('Employment Type'),
            '#options' => [
                'full-time'     => $this->t('Full-time'),
                'part-time'     => $this->t('Part-time'),
                'temporary'     => $this->t('Temporary'),
                'seasonal'      => $this->t('Seasonal'),
                'internship'    => $this->t('Internship'),
            ],
        ];

        $form['actions'] = [
            '#type' => 'actions',
        ];

        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Search'),
        ];

        return $form;
    }

    public function getFormId() {
        return 'jobposting_form';
    }

    /**
     * Implements a form submit handler.
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $form_state->setRebuild();
    }

}
