<?php
namespace MOC\Timelog\Command;

use MOC\Timelog\ElasticSearch\WorkUnitType;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;

class IndexCommandController extends CommandController {
	/**
	 * @Flow\Inject
	 * @var \Flowpack\ElasticSearch\Domain\Factory\ClientFactory
	 */
	protected $clientFactory;

	/**
	 * Import json file into ElasticSearch
	 *
	 * @param string $datafile The filename to import
	 */
	public function importTimelogDataFileCommand($datafile) {
		$jsonData = file_get_contents($datafile);

		$client = $this->clientFactory->create();
		$indexName = 'timelogdata';

		$workUnitIndex = $client->findIndex($indexName);
		$workUnitType = new WorkUnitType($workUnitIndex,'WorkUnit');

		foreach (explode(PHP_EOL, $jsonData) as $line) {
			$data = json_decode($line,TRUE);
			if ($data !== NULL) {
				try {
					$document = new \Flowpack\ElasticSearch\Domain\Model\Document($workUnitType, $data);
					$document->store();
				} catch (\Exception $e) {
					print "Error submitting entry to ES. Data: " . $line . PHP_EOL;
				}
			}
		}

	}


}