<?php

namespace Drupal\social_wall\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\social_wall\Plugin\SocialNetworkManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'SocialWallBlock' block.
 *
 * @Block(
 *  id = "social_wall_block",
 *  admin_label = @Translation("Social wall block"),
 * )
 */
class SocialWallBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The social network manager service.
   *
   * @var \Drupal\social_wall\Plugin\SocialNetworkManager
   */
  protected $socialNetworkManager;

  /**
   * SocialWallBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param array $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\social_wall\Plugin\SocialNetworkManager $social_network_manager
   *   The social network manager service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    array $plugin_definition,
    EntityTypeManager $entity_type_manager,
    SocialNetworkManager $social_network_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->socialNetworkManager = $social_network_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.social_network')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $group_class = 'group-order-weight';

    // Build table.
    $form['items'] = [
      '#type' => 'table',
      '#caption' => Markup::create('<h3>' . $this->t('Social networks') . '</h3>'),
      '#header' => [
        $this->t('Label'),
        $this->t('Machine name'),
        $this->t('Display'),
        $this->t('Weight'),
      ],
      '#empty' => $this->t('No social network found.'),
      '#tableselect' => FALSE,
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => $group_class,
        ],
      ],
    ];

    $social_network_configs = $this->entityTypeManager->getStorage('social_network_config')->loadMultiple();

    // Adapt delta according to results.
    $delta = 5;
    if (count($social_network_configs) > 10) {
      $delta = (int) (count($social_network_configs) / 2);
    }

    // Order SN by weight.
    usort($social_network_configs, function ($a, $b) {
      // Config not existing.
      if (empty($this->configuration['social_networks'][$a->id()]['weight'])) {
        return -1;
      }
      return $this->configuration['social_networks'][$a->id()]['weight'] <=> $this->configuration['social_networks'][$b->id()]['weight'];
    });

    // Build rows.
    foreach ($social_network_configs as $social_network_config) {
      $form['items'][$social_network_config->id()]['#attributes']['class'][] = 'draggable';
      $form['items'][$social_network_config->id()]['#weight'] = $this->configuration['social_networks'][$social_network_config->id()]['weight'] ?? 0;

      // Label.
      $form['items'][$social_network_config->id()]['label'] = [
        '#plain_text' => $social_network_config->label(),
      ];

      // Machine name.
      $form['items'][$social_network_config->id()]['id'] = [
        '#plain_text' => $social_network_config->id(),
      ];

      // Display.
      $form['items'][$social_network_config->id()]['display'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Display this social network'),
        '#title_display' => 'invisible',
        '#default_value' => $this->configuration['social_networks'][$social_network_config->id()]['display'] ?? 0,
      ];

      // Weight.
      $form['items'][$social_network_config->id()]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $social_network_config->label()]),
        '#title_display' => 'invisible',
        '#default_value' => $this->configuration['social_networks'][$social_network_config->id()]['weight'] ?? 0,
        '#attributes' => [
          'class' => [$group_class],
        ],
        '#delta' => $delta,
      ];
    }

    // Form action buttons.
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    if (!empty($form_state->getValue('items'))) {
      // Store configuration.
      foreach ($form_state->getValue('items') as $id => $social_network_config_params) {
        $this->configuration['social_networks'][$id]['display'] = $social_network_config_params['display'];
        $this->configuration['social_networks'][$id]['weight'] = $social_network_config_params['weight'];
      }
    }

    if (!empty($form['id']['#value'])) {
      // Store block ID in configuration.
      $this->configuration['block_id'] = $form['id']['#value'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [
      '#theme' => 'social_wall__block',
      '#elements' => [],
      '#cache' => [
        'tags' => [
          'config:social_wall.social_network_config',
          'social_network_config_list',
        ],
      ],
    ];

    // Display only selected SN.
    foreach ($this->configuration['social_networks'] as $id => $form_config) {
      if ($form_config['display']) {
        // Get related config entity.
        $social_network_config = $this->entityTypeManager->getStorage('social_network_config')->load($id);
        if (!empty($social_network_config)) {
          // Get related settings.
          $third_party_settings = $social_network_config->getThirdPartySetting('social_wall', 'sn_config', [])[$social_network_config->id()] ?? [];
          // Get related plugin.
          $plugin = $this->socialNetworkManager->createInstance($social_network_config->getWidget(), $third_party_settings);
          // Add SN render to block build.
          $build['#elements'][$social_network_config->id()] = $plugin->render();
          // Set block weight.
          $build['#elements'][$social_network_config->id()]['#weight'] = $form_config['weight'];
        }
      }
    }

    // Sort displayable SN by weight.
    usort($build['#elements'], function ($a, $b) {
      return $a['#weight'] <=> $b['#weight'];
    });

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = [];
    // Adding context configuration as a cache tag to invalidate block cache
    // when the context has been modified.
    if (isset($this->configuration['context_id'])) {
      $tags[] = 'config:context.context.' . $this->configuration['context_id'];
    }

    // Invalidate cache when block layout configuration has been modified.
    if (isset($this->configuration['block_id'])) {
      $tags[] = 'config:block.block.' . $this->configuration['block_id'];
    }

    return Cache::mergeTags(parent::getCacheTags(), $tags);
  }

}
