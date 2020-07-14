<?php

namespace Drupal\block_form\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Rating' Block.
 *
 * @Block(
 *   id = "rating_block",
 *   admin_label = @Translation("Add Rating"),
 *   category = @Translation("Add Rating "),
 * )
 */
class RatingBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    
  
 return \Drupal::formBuilder()->getForm('Drupal\block_form\Form\BlockForm');
 

  }

}
