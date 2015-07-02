<?php
namespace MOC\Timelog\ElasticSearch;

class WorkUnitType extends \Flowpack\ElasticSearch\Domain\Model\AbstractType {

	/**
	 * @return string
	 */
	public function getName() {
		return 'Timelogentry';
	}

}