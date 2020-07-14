<?php
/**
 * @file
 * Contains \Drupal\block_form\Form\BlockForm.
 */
namespace Drupal\block_form\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Database\Database;
use Drupal\Core\Messenger\MessengerTrait;
class BlockForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'api_form';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
   $form['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Name:'),
      '#required' => TRUE,
    );
    $form['rating'] = array (
      '#type' => 'radios',
      '#title' => ('Rating'),
      '#options' => array(
        '1' => t('1'),
        '2' => t('2'),
        '3' => t('3'),
        '4' => t('4'),
        '5' => t('5'),
      ),
    
   );
    
   


    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );
    return $form;
  }



// validate api key length

  // public function validateForm(array &$form, FormStateInterface $form_state) {
  //     if (strlen($form_state->getValue('apikey')) < 16) {
  //       $form_state->setErrorByName('apikey', $this->t('API key is not valid.'));
  //     }
  //   }
 
// storing data to the database table api
  public function submitForm(array &$form, FormStateInterface $form_state) {
  $conn = Database::getConnection();
  $conn->insert('rating_data')->fields(
    array(
      'name' => $form_state->getValue('name'),
      'rating' => $form_state->getValue('rating'),

    )
  )
  ->execute();
  $this->messenger()->addMessage('API added successfully');
   
 }


}