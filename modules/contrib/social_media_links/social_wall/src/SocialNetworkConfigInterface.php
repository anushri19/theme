<?php

namespace Drupal\social_wall;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a Social network config entity.
 */
interface SocialNetworkConfigInterface extends ConfigEntityInterface {

  /**
   * Get the selected widget.
   *
   * @return string
   *   The widget name.
   */
  public function getWidget();

}
