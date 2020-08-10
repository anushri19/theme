<?php

namespace Drupal\social_wall\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Social network plugin manager.
 */
class SocialNetworkManager extends DefaultPluginManager {

  /**
   * Constructs a new SocialNetworkManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/SocialNetwork', $namespaces, $module_handler, 'Drupal\social_wall\Plugin\SocialNetworkInterface', 'Drupal\social_wall\Annotation\SocialNetwork');

    $this->alterInfo('social_wall_social_network_info');
    $this->setCacheBackend($cache_backend, 'social_wall_social_network_plugins');
  }

}
