<?php

namespace Drupal\social_wall\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Social network plugins.
 */
interface SocialNetworkInterface extends PluginInspectionInterface {

  /**
   * Get the plugin label.
   *
   * @return string
   *   The plugin label.
   */
  public function getLabel();

  /**
   * Get the plugin settings form.
   *
   * @param array $settings
   *   The third party settings data.
   *
   * @return array
   *   The settings form.
   */
  public function settingsForm(array $settings);

  /**
   * Get the plugin render array.
   *
   * @return array
   *   A render array.
   */
  public function render();

}
