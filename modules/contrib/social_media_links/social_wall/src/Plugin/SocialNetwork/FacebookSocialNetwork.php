<?php

namespace Drupal\social_wall\Plugin\SocialNetwork;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\social_wall\Plugin\SocialNetworkBase;
use Exception;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;

/**
 * Class FacebookSocialNetwork.
 *
 * @package Drupal\social_wall\Plugin\SocialNetwork
 *
 * @SocialNetwork(
 *   id = "facebook_social_network",
 *   label = @Translation("Facebook social network")
 * )
 */
class FacebookSocialNetwork extends SocialNetworkBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return 'Facebook';
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $third_party_settings = []) {
    $form = [];

    $form['app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->translationManager->translate('Application ID'),
      '#default_value' => $third_party_settings['app_id'] ?? '',
    ];

    $form['secret_key'] = [
      '#type' => 'textfield',
      '#title' => $this->translationManager->translate('Secret key'),
      '#default_value' => $third_party_settings['secret_key'] ?? '',
    ];

    $form['access_token'] = [
      '#type' => 'textfield',
      '#title' => $this->translationManager->translate('Access token'),
      '#default_value' => $third_party_settings['access_token'] ?? '',
      '#maxlength' => 200,
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
    $cached_results = $this->cacheBackend->get('social_wall_facebook_' . $this->configuration['access_token']);
    if ($cached_results && ($cached_results->valid)) {
      return $cached_results->data;
    }

    try {
      if (!empty($app_id = $this->configuration['app_id']) &&
        !empty($secret_key = $this->configuration['secret_key']) &&
        !empty($access_token = $this->configuration['access_token'])) {

        // Retreive last post.
        $fb = new Facebook([
          'app_id' => $app_id,
          'app_secret' => $secret_key,
          'default_access_token' => $access_token,
          'default_graph_version' => 'v3.2',
        ]);

        try {
          /** @var \Facebook\FacebookResponse $response */
          $response = $fb->get('/me/posts?fields=id,message,created_time,permalink_url&limit=' . $this->configuration['nb_of_posts']);
        }
        catch (FacebookResponseException $e) {
          // Graph returns an error.
          $this->loggerFactory->get('social_wall')->error('Graph returned an error: @error', ['@error' => $e->getMessage()]);
        }
        catch (FacebookSDKException $e) {
          // Validation fails or other local issues.
          $this->loggerFactory->get('social_wall')->error('Facebook SDK returned an error: @error', ['@error' => $e->getMessage()]);
        }

        // If response is empty.
        if (empty($response)) {
          return [];
        }

        $data = json_decode($response->getBody())->data;

        if (!$response->isError() && !empty($data)) {
          $build = [
            '#theme' => 'social_network_facebook_block',
            '#elements' => [],
          ];

          foreach ($data as $item) {
            $text = strlen($item->message) < 145 ? $item->message : substr($item->message, 0, 145) . '...';

            $build['#elements'][] = [
              'creation_timestamp' => strtotime($item->created_time),
              'body_text' => $text,
              'post_url' => $item->permalink_url,
            ];
          }

          // Cache data.
          $this->cacheBackend->set('social_wall_facebook_' . $this->configuration['access_token'], $build, time() + self::getDataCacheTime());
        }
      }
    }
    catch (Exception $e) {
      $this->loggerFactory->get('social_wall')->error('Facebook : @error', ['@error' => $e->getMessage()]);
    }

    // Set block cache.
    $build['#cache']['max-age'] = self::getDataCacheTime();

    return $build;
  }

}
