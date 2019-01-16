<?php

/**
 * @file
 * Custom Rest Controller to return latest nodes.
 */

namespace Drupal\hydramaze_tutorial\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class HydramazeTutorialController extends ControllerBase {

	protected $entity_type_manager;

	public function __construct(EntityTypeManagerInterface $entity_type_manager) {
		$this->entity_type_manager = $entity_type_manager;
	}

	public static function create(ContainerInterface $container) {
		return new static(
			$container->get('entity_type.manager')
		);
	}

	public function getAlgorithmsList() {
		$response_array = [];

		$node_query = $this->entity_type_manager->getStorage('node');
		$result = $node_query->getQuery()
			->condition('status', NodeInterface::PUBLISHED)
			->condition('type', 'algorithm')
			->execute();
		if ($result) {
			$nodes = $this->entityTypeManager()->getStorage('node')->loadMultiple($result);
			foreach ($nodes as $key => $node) {
				$response_array[$key] = [
					'id' => $key,
					'name' => $node->title->value,
          'simpleDescription' => $node->body->value,
					'learningType' => $node->field_learning_type->entity->getName(),
					'type' => $node->field_type->entity->getName(),
				];

        foreach ($node->field_references->referencedEntities() as $reference) {
				 	$response_array[$key]['references'][] = [
				 	  'description' => $reference->label(),
            'type' => $reference->field_reference_type->value,
				 	  'url' => $reference->field_reference_url->value,
          ];
        }
			}
		}
		else {
			$response_array = ['message' => 'No nodes'];
		}

		$cache_metadata = new CacheableMetadata();
		$cache_metadata->setCacheTags(['node_list']);

//		$response = new CacheableJsonResponse($response_array);
//		$response->addCacheableDependency($cache_metadata);
    $response = new Response();
    $response->setContent(json_encode($response_array));
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function getAlgorithmParameters($nid) {
	  $response_array = [];

    $parameters = [];
    $node = $this->entityTypeManager()->getStorage('node')->load($nid);

    foreach ($node->field_parameter->referencedEntities() as $parameter) {
      $parameters[] = [
        'name' => $parameter->label(),
        'component' => $parameter->field_component->value,
        'maxValue' => $parameter->field_max_value->value,
        'minValue' => $parameter->field_min_value->value,
        'defaultValue' => $parameter->field_default_value->value,
        'simpleDescription' => $parameter->body->value,
        'references' => array_map(function ($reference) {
          return [
            'description' => $reference->label(),
            'type' => $reference->field_reference_type->value,
            'url' => $reference->field_reference_url->value,
          ];
        }, $parameter->field_references->referencedEntities()),
      ];
    }

    $response = new Response();
    $response->setContent(json_encode($parameters));
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Content-Type', 'application/json');
    return $response;
  }
}