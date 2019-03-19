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
                // dump($item);die();
                $file = File::load($item['photo']);
                $photos_array[] = [
                    'url' => file_create_url($file->getFileUri()),
                    'alt' => $item['alt'],
                ];
            }
        }

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
        // dump($items);

        $form['#tree'] = TRUE;

        $form['photos'] = [
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
            $form['photos'][$i]['photo'] = [
                '#type' => 'managed_file',
                '#title' => t('Photo'),
                '#description' => t('Extensions autorisées'). ': gif png jpg jpeg',
                '#upload_validators' => array(
                    'file_validate_extensions' => array('gif png jpg jpeg'),
                ),
                '#upload_location' => 'public://photo',
                '#default_value' => $items[$i],
                '#required' => FALSE,
            ];

            $form['photos'][$i]['alt'] = [
                '#type' => 'textfield',
                '#title' => t('Description'),
                '#default_value' => $items[$i]['alt'],
                '#required' => FALSE,
            ];
        }

        $form['photos']['actions'] = [
            '#type' => 'actions',
        ];

        $form['photos']['actions']['add_item'] = [
            '#type' => 'submit',
            '#value' => t('Add'),
            '#submit' => [[$this, 'addOne']],
            '#ajax' => [
                'callback' => [$this, 'addmoreCallback'],
                'wrapper' => 'items-fieldset-wrapper',
            ],
        ];

        if ($name_field > 1) {
            $form['photos']['actions']['remove_item'] = [
                '#type' => 'submit',
                '#value' => t('Remove'),
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
        return $form['settings']['photos'];
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
        $this->configuration['photos'] = null;
        $photos = $form_state->getValues();
        if (isset($photos['photos'])) {
            foreach ($photos['photos'] as $key => $value) {
                $items = $value['photo'];
                foreach ($items as $key => $photo) {
                    if ($photo === '' || !$photo) {
                        unset($items[$key]);
                    }
                    $item = [
                        'photo' => $value['photo'][0],
                        'alt' => $value['alt'],
                    ];
                    $this->configuration['photos'][] = $item;
                }
            }
        }
    }

}
