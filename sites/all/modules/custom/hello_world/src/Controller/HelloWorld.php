<?php
/**
 * @file
 * Contains \Drupal\hello_world\Controller\FirstController.
 */
 
namespace Drupal\hello_world\Controller;
 
use Drupal\Core\Controller\ControllerBase;
 
class HelloWorld extends ControllerBase {
  public function content() {
    return array(
      '#type' => 'markup',
      '#markup' => t('Hello world'),
    );
  }
}