<?php

namespace Drupal\social_wall\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\social_wall\SocialNetworkConfigInterface;

/**
 * Defines the Social network config entity.
 *
 * @ConfigEntityType(
 *   id = "social_network_config",
 *   label = @Translation("Social network config"),
 *   handlers = {
 *     "list_builder" = "Drupal\social_wall\Controller\SocialNetworkConfigListBuilder",
 *     "form" = {
 *       "add" = "Drupal\social_wall\Form\SocialNetworkConfigForm",
 *       "edit" = "Drupal\social_wall\Form\SocialNetworkConfigForm",
 *       "delete" = "Drupal\social_wall\Form\SocialNetworkConfigDeleteForm",
 *     }
 *   },
 *   config_prefix = "social_network_config",
 *   admin_permission = "administer social networks",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "widget" = "widget"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "widget"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/services/social_wall/{social_network_config}",
 *     "delete-form" = "/admin/config/services/social_wall/{social_network_config}/delete",
 *   }
 * )
 */
class SocialNetworkConfig extends ConfigEntityBase implements SocialNetworkConfigInterface {

  /**
   * The Social network config ID.
   *
   * @var string
   */
  public $id;

  /**
   * The Social network config label.
   *
   * @var string
   */
  public $label;

  /**
   * The Social network config widget.
   *
   * @var string
   */
  protected $widget;

  /**
   * The Social network config weight.
   *
   * @var int
   */
  protected $weight;

  /**
   * Get the selected widget.
   *
   * @return string
   *   The widget type.
   */
  public function getWidget() {
    return $this->widget;
  }

}
