<?php
/**
 * @file
 * Contains \Drupal\resume\Form\ResumeForm.
 */
namespace Drupal\node_json_data\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Database\Database;
use Drupal\Core\Messenger\MessengerTrait;
class APIform extends FormBase {
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
    $form['apikey'] = array(
      '#type' => 'textfield',
      '#title' => t('API Key:'),
      '#required' => TRUE,
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

  public function validateForm(array &$form, FormStateInterface $form_state) {
      if (strlen($form_state->getValue('apikey')) < 16) {
        $form_state->setErrorByName('apikey', $this->t('API key is not valid.'));
      }
    }
 
// storing data to the database table api
  public function submitForm(array &$form, FormStateInterface $form_state) {
  $conn = Database::getConnection();
  $conn->insert('api')->fields(
    array(
      'apikey' => $form_state->getValue('apikey'),
    )
  )
  ->execute();
  $this->messenger()->addMessage('API added successfully');
   
 }


}
