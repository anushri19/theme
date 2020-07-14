<?php

namespace Drupal\node_json_data\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Database\Database;

class getApiDataController extends ControllerBase {
    

public function getApiDataController($apiKey, $node_id) {
 
 $header_table = array(
 'id'=> t('id'),
 'apikey' => t('apikey'),
 
 );
//select records from table
 $query = \Drupal::database()->select('api', 'm');
 $query->fields('m', ['id','apikey']);
 $query->condition('m.apikey', $apiKey);
$query->condition('m.id', $node_id);

  
 $results = $query->execute()->fetchAll();
 $rows=array();
 foreach($results as $data) {


//print the data from table
    $rows[] = array(
    'id' =>$data->id,
    'apikey' => $data->apikey,
 
    
 );
}
 //display data in site
 $form['table'] = [
 '#type' => 'table',
 '#header' => $header_table,
 '#rows' => $rows,
 '#empty' => t('No users found'),
 ];
 return new JsonResponse( $form );
}}
