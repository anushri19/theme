<?php

namespace Drupal\social_wall\Plugin\SocialNetwork;

use Abraham\TwitterOAuth\TwitterOAuth;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\social_wall\Plugin\SocialNetworkBase;
use Exception;

/**
 * Class TwitterSocialNetwork.
 *
 * @package Drupal\social_wall\Plugin\SocialNetwork
 *
 * @SocialNetwork(
 *   id = "twitter_social_network",
 *   label = @Translation("Twitter social network")
 * )
 */
class TwitterSocialNetwork extends SocialNetworkBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return 'Twitter';
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $third_party_settings = []) {
    $form = [];

    $form['account_name'] = [
      '#type' => 'textfield',
      '#title' => $this->translationManager->translate('Account name'),
      '#default_value' => $third_party_settings['account_name'] ?? '',
      '#required' => TRUE,
    ];

    $form['consumer_key'] = [
      '#type' => 'textfield',
      '#title' => $this->translationManager->translate('Consumer key'),
      '#default_value' => $third_party_settings['consumer_key'] ?? '',
      '#required' => TRUE,
    ];

    $form['consumer_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->translationManager->translate('Consumer secret'),
      '#default_value' => $third_party_settings['consumer_secret'] ?? '',
      '#required' => TRUE,
    ];

    $form['access_token'] = [
      '#type' => 'textfield',
      '#title' => $this->translationManager->translate('Access token'),
      '#default_value' => $third_party_settings['access_token'] ?? '',
      '#required' => TRUE,
    ];

    $form['access_token_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->translationManager->translate('Access token secret'),
      '#default_value' => $third_party_settings['access_token_secret'] ?? '',
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
    $cached_results = $this->cacheBackend->get('social_wall_twitter_' . $this->configuration['access_token']);
    if ($cached_results && ($cached_results->valid)) {
      return $cached_results->data;
    }

    try {
      if (!empty($account_name = $this->configuration['account_name']) &&
        !empty($consumer_key = $this->configuration['consumer_key']) &&
        !empty($consumer_secret = $this->configuration['consumer_secret']) &&
        !empty($access_token = $this->configuration['access_token']) &&
        !empty($access_token_secret = $this->configuration['access_token_secret'])) {

        $connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
        $response = $connection->get('statuses/user_timeline', [
          'screen_name' => $account_name,
          'count' => $this->configuration['nb_of_posts'],
          'tweet_mode' => 'extended',
          'exclude_replies' => FALSE,
          'include_rts' => TRUE,
        ]);

        if ($connection->getLastHttpCode() == 200 && !empty($response)) {
          $build = [
            '#theme' => 'social_network_twitter_block',
            '#elements' => [],
          ];

          foreach ($response as $item) {
            $text = strlen($item->full_text) < 145 ? $item->full_text : substr($item->full_text, 0, 145) . '...';
            $post_url = 'https://twitter.com/' . $item->user->screen_name . '/status/' . $item->id;

            $build['#elements'][] = [
              'creation_timestamp' => strtotime($item->created_at),
              'body_text' => $text,
              'post_url' => $post_url,
            ];
          }

          // Cache data.
          $this->cacheBackend->set('social_wall_twitter_' . $this->configuration['access_token'], $build, time() + self::getDataCacheTime());
        }
      }
    }
    catch (Exception $e) {
      $this->loggerFactory->get('social_wall')->error('Twitter : @error', ['@error' => $e->getMessage()]);
    }

    // Set block cache.
    $build['#cache']['max-age'] = self::getDataCacheTime();

    return $build;
  }

}
