<?php

namespace Drupal\social_wall\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\social_wall\Plugin\SocialNetworkManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for the Social network config add and edit forms.
 */
class SocialNetworkConfigForm extends EntityForm {

  /**
   * The social network manager.
   *
   * @var \Drupal\social_wall\Plugin\SocialNetworkManager
   */
  protected $socialNetworkManager;

  /**
   * Constructs an SocialNetworkConfigForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\social_wall\Plugin\SocialNetworkManager $social_network_manager
   *   The social network manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, SocialNetworkManager $social_network_manager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->socialNetworkManager = $social_network_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.social_network')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\social_wall\Entity\SocialNetworkConfig $social_network_config */
    $social_network_config = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $social_network_config->label(),
      '#description' => $this->t('Label for the Social network.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $social_network_config->id(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
      ],
      '#disabled' => !$social_network_config->isNew(),
    ];

    $plugin_definitions = $this->socialNetworkManager->getDefinitions();
    foreach ($plugin_definitions as $plugin_id => $plugin_definition) {
      $plugin = $this->socialNetworkManager->createInstance($plugin_id);
      $options[$plugin_id] = $plugin->getLabel();
    }

    $form['widget'] = [
      '#type' => 'select',
      '#title' => $this->t('Widget'),
      '#options' => $options ?? [],
      '#default_value' => $social_network_config->getWidget(),
      '#description' => $this->t('Select a social network.'),
      '#required' => TRUE,
      '#ajax' => [
        'event' => 'change',
        'callback' => [get_called_class(), 'ajaxUpdateForm'],
      ],
    ];

    $form['widget_config'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Social network configuration'),
      '#prefix' => '<div id="sn-config">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    ];

    $select_value = $form_state->getValue('widget') ?? $social_network_config->getWidget();
    $third_party_settings = $social_network_config->getThirdPartySetting('social_wall', 'sn_config', [])[$social_network_config->id()] ?? [];
    if (!empty($select_value)) {
      $plugin = $this->socialNetworkManager->createInstance($select_value);
      $form['widget_config']['config'] = $plugin->settingsForm($third_party_settings);
    }
    else {
      $form['widget_config']['config'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $this->t('No available configuration.'),
        '#attributes' => [
          'class' => ['no-value'],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\social_wall\Entity\SocialNetworkConfig $social_network_config */
    $social_network_config = $this->entity;

    // Save plugin config to 3rd party settings.
    $social_network_config->setThirdPartySetting('social_wall', 'sn_config', [
      $social_network_config->id() => $social_network_config->widget_config['config'],
    ]);

    $status = $social_network_config->save();

    if ($status) {
      $this->messenger()->addMessage($this->t('Saved the %label Social network.', [
        '%label' => $social_network_config->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('The %label Social network was not saved.', [
        '%label' => $social_network_config->label(),
      ]), MessengerInterface::TYPE_ERROR);
    }

    $form_state->setRedirect('entity.social_network_config.collection');
  }

  /**
   * Check whether a Social network configuration entity exists or not.
   *
   * @param string $id
   *   The machine name.
   *
   * @return bool
   *   Weither the machine name already exists.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function exist($id) {
    $entity = $this->entityTypeManager->getStorage('social_network_config')->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

  /**
   * Return the Ajax response for configuration form.
   *
   * @param array $form
   *   The The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The Ajax response.
   */
  public function ajaxUpdateForm(array &$form, FormStateInterface &$form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#sn-config', $form['widget_config']));
    return $response;
  }

}
