<?php
namespace MOC\Timelog\Command;

use MOC\Timelog\ElasticSearch\WorkUnitMapping;
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

		if (!$workUnitIndex->exists()) {
			$workUnitIndex->create();
		}
		$workUnitIndex->request('POST', '_flush');

		$workUnitType = new WorkUnitType($workUnitIndex, 'WorkUnit');
		$workUnitMapping = new WorkUnitMapping($workUnitType);
		$workUnitMapping->setPropertyByPath('customerName', array('type' => 'string', 'index' => 'not_analyzed'));
		$workUnitMapping->setPropertyByPath('employeeInitials', array('type' => 'string', 'index' => 'not_analyzed'));
		$workUnitMapping->setPropertyByPath('employeeName', array('type' => 'string', 'index' => 'not_analyzed'));
		$workUnitMapping->setPropertyByPath('projectName', array('type' => 'string', 'index' => 'not_analyzed'));
		$workUnitMapping->apply();

		$lines = explode(PHP_EOL, $jsonData);
		$linesCount = count($lines);
		$this->output->progressStart($linesCount);
		foreach ($lines as $line) {
			$data = json_decode($line, TRUE);
			$this->output->progressAdvance();
			if ($data !== NULL) {
				try {
					$document = new \Flowpack\ElasticSearch\Domain\Model\Document($workUnitType, $data);
					$document->store();
				} catch (\Exception $e) {
					print 'Error submitting entry to ES. Data: ' . $line . PHP_EOL;
				}
			}
		}
		$this->output->progressFinish();
	}

}