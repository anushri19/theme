<?php

namespace Drupal\social_wall\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\TranslationManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Social network plugins.
 */
abstract class SocialNetworkBase extends PluginBase implements SocialNetworkInterface {

  /**
   * The translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  protected $translationManager;

  /**
   * The cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Time for caching API call data, to prevent "exceeded quota" error.
   *
   * @var int
   */
  protected static $dataCacheTime = 60 * 15;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    TranslationManager $translation_manager,
    CacheBackendInterface $cache_backend,
    LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->translationManager = $translation_manager;
    $this->cacheBackend = $cache_backend;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('string_translation'),
      $container->get('cache.default'),
      $container->get('logger.factory')
    );
  }

  /**
   * Return the cache time for API data.
   *
   * @return int
   *   The number of seconds for cache time.
   */
  public static function getDataCacheTime() {
    return self::$dataCacheTime;
  }

}
