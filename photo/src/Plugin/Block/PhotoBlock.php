<?php
/**
 * PhotoBlock
 * @author Adrien Durmier
 */

namespace Drupal\photo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Provides a 'Photo' Block.
 *
 * @Block(
 *   id = "photo_block",
 *   admin_label = @Translation("Photos Gallery block"),
 * )
 */
class PhotoBlock extends BlockBase implements BlockPluginInterface{

    /**
     * {@inheritdoc}
     */
    public function build() {
        $photos = $this->configuration['photos'];

        $photos_array = [];
        if ($photos) {
            foreach ($photos as $item){
                $file = File::load($item[0]);
                $photos_array[] = file_create_url($file->getFileUri());
            }
        }

        //dump($tests);

        return array(
            '#theme' => 'photo_block',
            '#photos' => $photos_array,
            '#attached' => array(
                'library' => array(
                    'photo/photo_assets',
                ),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state) {
        $form = parent::blockForm($form, $form_state);

        $config = $this->getConfiguration();

        $items = [];
        if ($config['photos']) {
            $items = $config['photos'];
        }


        $form['#tree'] = TRUE;

        $form['items_fieldset'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('Photos'),
            '#prefix' => '<div id="items-fieldset-wrapper">',
            '#suffix' => '</div>',
        ];

        if (!$form_state->has('num_items')) {
            $form_state->set('num_items', count($config['photos']));
        }
        $name_field = $form_state->get('num_items');
        for ($i = 0; $i < $name_field; $i++) {
            $items = array_values($items);
            $form['items_fieldset']['items'][$i] = [
                '#type' => 'managed_file',
                '#title' => t('Photo'),
                '#description' => t('Extensions autorisées'). ': gif png jpg jpeg',
                '#upload_validators' => array(
                    'file_validate_extensions' => array('gif png jpg jpeg'),
                ),
                '#upload_location' => 'public://photo',
                '#default_value' => $items[$i],
                '#required' => TRUE,
            ];
        }

        $form['items_fieldset']['actions'] = [
            '#type' => 'actions',
        ];

        $form['items_fieldset']['actions']['add_item'] = [
            '#type' => 'submit',
            '#value' => t('Add'),
            '#submit' => [[$this, 'addOne']],
            '#ajax' => [
                'callback' => [$this, 'addmoreCallback'],
                'wrapper' => 'items-fieldset-wrapper',
            ],
        ];

        if ($name_field > 1) {
            $form['items_fieldset']['actions']['remove_item'] = [
                '#type' => 'submit',
                '#value' => t('Remove one'),
                '#submit' => [[$this, 'removeCallback']],
                '#ajax' => [
                    'callback' => [$this, 'addmoreCallback'],
                    'wrapper' => 'items-fieldset-wrapper',
                ]
            ];
        }

        return $form;
    }

    /**
     * @param array $form
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     */
    public function addOne(array &$form, FormStateInterface $form_state) {
        $name_field = $form_state->get('num_items');
        $add_button = $name_field + 1;
        $form_state->set('num_items', $add_button);
        $form_state->setRebuild();
    }

    /**
     * @param array $form
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     * @return mixed
     */
    public function addmoreCallback(array &$form, FormStateInterface $form_state) {
        return $form['settings']['items_fieldset'];
    }

    /**
     * @param array $form
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     */
    public function removeCallback(array &$form, FormStateInterface $form_state) {
        $name_field = $form_state->get('num_items');
        if ($name_field > 1) {
            $remove_button = $name_field - 1;
            $form_state->set('num_items', $remove_button);
        }
        $form_state->setRebuild();
    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state) {
        foreach ($form_state->getValues() as $key => $value) {
            if ($key === 'items_fieldset') {
                if (isset($value['items'])) {
                    $items = $value['items'];
                    foreach ($items as $key => $item) {
                        if ($item === '' || !$item) {
                            unset($items[$key]);
                        }
                    }
                    $this->configuration['photos'] = $items;
                }
            }
        }
    }

}
