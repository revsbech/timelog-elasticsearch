<?php
namespace MOC\Timelog\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "MOC.Timelog".           *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class StandardController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @Flow\Inject
	 * @var \Flowpack\ElasticSearch\Domain\Factory\ClientFactory
	 */
	protected $clientFactory;

	/**
	 * @return void
	 */
	public function indexAction() {
		$client = $this->clientFactory->create();
		$index = $client->findIndex('timelogdata');

		$query = '{
			"size": 0,
			"aggregations": {
				"invoiced_over_time": {
					"date_histogram" : {
						 "field" : "date",
						 "interval" : "month",
						 "time_zone": "Europe/Copenhagen"
					},
					"aggregations": {
						"sum_amount": {
							"sum": {
								"field": "invAmount"
							}
						}
					}
				}
			}
		}';

		$response = $index->request('GET', '/_search', array(), $query);
		$temp = $response->getTreatedContent();

		$data = array();
		foreach ($temp['aggregations']['invoiced_over_time']['buckets'] as $bucket) {
			$data[] = array(
				'date' => date('Y-m', $bucket['key']/ 1000), //Time is in milliseconds
				'value' => $bucket['sum_amount']['value']
			);
		}
		$this->view->assign('data', $data);
	}

}