<?php

/**
 * @file
 * Custom Rest Controller to return latest nodes.
 */

namespace Drupal\custom_rest_test\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\HttpFoundation\Response;

class CustomRestController extends ControllerBase {

	protected $entity_query;

	public function __construct(QueryFactory $entity_query) {
		$this->entity_query = $entity_query;
	}

	public static function create(ContainerInterface $container) {
		return new static(
			$container->get('entity.query')
		);
	}

	public function getLatestNodes() {
		$response_array = [];

		$node_query = $this->entity_query->get('node')
			->condition('status', 1)
			->condition('type', 'algorithm')
			->execute();
		if ($node_query) {
			$nodes = $this->entityTypeManager()->getStorage('node')->loadMultiple($node_query);
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

		$response = new CacheableJsonResponse($response_array);
		$response->addCacheableDependency($cache_metadata);
//    $response = new Response();
//    $response->setContent(json_encode($response_array));
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Content-Type', 'application/json');

		return $response;
	}
}