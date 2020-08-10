<?php

namespace Drupal\social_wall\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Social network item annotation object.
 *
 * @see \Drupal\social_wall\Plugin\SocialNetworkManager
 * @see plugin_api
 *
 * @Annotation
 */
class SocialNetwork extends Plugin {


  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
