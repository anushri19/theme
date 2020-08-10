<?php

namespace Drupal\social_wall\Plugin\SocialNetwork;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\social_wall\Plugin\SocialNetworkBase;
use Exception;

/**
 * Class InstagramSocialNetwork.
 *
 * @package Drupal\social_wall\Plugin\SocialNetwork
 *
 * @SocialNetwork(
 *   id = "instagram_social_network",
 *   label = @Translation("Instagram social network")
 * )
 */
class InstagramSocialNetwork extends SocialNetworkBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return 'Instagram';
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $third_party_settings = []) {
    $form = [];

    $form['token'] = [
      '#type' => 'textfield',
      '#title' => $this->translationManager->translate('Token'),
      '#description' => $this->translationManager->translate('The Instagram token.'),
      '#default_value' => $third_party_settings['token'] ?? '',
      '#required' => TRUE,
    ];

    $form['nb_of_posts'] = [
      '#type' => 'select',
      '#title' => $this->translationManager->translate('Number of posts'),
      '#description' => $this->translationManager->translate('The amount of posts to display.'),
      '#options' => array_combine(range(1, 10), range(1, 10)),
      '#default_value' => $third_party_settings['nb_of_posts'] ?? 1,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = [];

    // If data has been cached, return cached data.
    $cached_results = $this->cacheBackend->get('social_wall_instagram_' . $this->configuration['token']);
    if ($cached_results && ($cached_results->valid)) {
      return $cached_results->data;
    }

    $token = $this->configuration['token'];
    $nb_of_posts = $this->configuration['nb_of_posts'];
    $url = "https://api.instagram.com/v1/users/self/media/recent/?access_token=$token&count=$nb_of_posts";

    try {
      $client = \Drupal::httpClient();
      $request = $client->get($url);
      $response = json_decode($request->getBody());

      if ($request->getStatusCode() == 200 && !empty($response->data)) {
        $build = [
          '#theme' => 'social_network_instagram_block',
          '#elements' => [],
        ];
        foreach ($response->data as $item) {
          // Truncate text after 145 characters.
          $text = strlen($item->caption->text) < 145 ? $item->caption->text : substr($item->caption->text, 0, 145) . '...';

          $build['#elements'][] = [
            'image_url' => $item->images->standard_resolution->url,
            'creation_timestamp' => $item->created_time,
            'body_text' => $text,
            'post_url' => $item->link,
          ];
        }

        // Cache data.
        $this->cacheBackend->set('social_wall_instagram_' . $this->configuration['token'], $build, time() + self::getDataCacheTime());
      }
    }
    catch (Exception $e) {
      $this->loggerFactory->get('social_wall')->error('Instagram : @error', ['@error' => $e->getMessage()]);
    }

    // Set block cache.
    $build['#cache']['max-age'] = self::getDataCacheTime();

    return $build;
  }

}
