<?php
/**
 * VideoBlock
 * @author Adrien Durmier
 */

namespace Drupal\video\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides a 'Video' Block.
 *
 * @Block(
 *   id = "video_block",
 *   admin_label = @Translation("Videos Gallery block"),
 * )
 */
class VideoBlock extends BlockBase implements BlockPluginInterface{

    /**
     * {@inheritdoc}
     */
    public function build() {
        $config = \Drupal::config('maquette.video.settings');
        $api_key = $config->get('maquette.video.api_key');
        $playlist_id = $this->configuration['maquette_video_playlist_id'];

        // Récupération des vidéos
        $playlist_url = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=50&playlistId=$playlist_id&key=$api_key";
        $playlist = file_get_contents($playlist_url);
        $obj_playlist = json_decode($playlist);

        return array(
            '#theme' => 'video_block',
            '#playlist' => $obj_playlist,
            '#attached' => array(
                'library' => array(
                    'video/video_assets',
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

        $form['playlist_id'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Playlist ID'),
            '#description' => $this->t("If the URL is <em>https://www.youtube.com/watch?v=BrQxjy7dhVY&list=PLs9bX4IVxwxwgg9Vg8Mf7-cFiL8kondo-</em> the playlist ID will be <em>PLs9bX4IVxwxwgg9Vg8Mf7-cFiL8kondo-</em>"),
            '#default_value' => isset($config['maquette_video_playlist_id']) ? $config['maquette_video_playlist_id'] : '',
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state) {
        parent::blockSubmit($form, $form_state);
        $values = $form_state->getValues();
        $this->configuration['maquette_video_playlist_id'] = $values['playlist_id'];
    }

}
