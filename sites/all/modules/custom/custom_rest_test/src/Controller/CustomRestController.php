<?php

/**
 * @file
 * Custom Rest Controller to return latest nodes.
 */

namespace Drupal\custom_rest_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;

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
			->execute();
		if ($node_query) {
			$nodes = $this->entityTypeManager()->getStorage('node')->loadMultiple($node_query);
			// var_dump($nodes);
			foreach ($nodes as $node) {
				$response_array[] = [
					'title' => $node->title->value,
					'parameters' => $node->field_algorithm_parameter->value,
				];
			}
		}
		else {
			$response_array = ['message' => 'No nodes'];
		}

		$response = new Response();
		$response->setContent(json_encode($response_array));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
}