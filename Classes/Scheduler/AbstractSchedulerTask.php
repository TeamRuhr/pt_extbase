<?php
namespace PunktDe\PtExtbase\Scheduler;

/***************************************************************
 *  Copyright (C) 2015 punkt.de GmbH
 *  Authors: el_equipo <opiuqe_le@punkt.de>
 *
 *  This script is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Scheduler\Task\AbstractTask;
use PunktDe\PtExtbase\Utility\TimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract Scheduler Task
 * use for Time logging of a scheduler task
 *
 * @package pt_extbase
 * @subpackage Scheduler
 */
abstract class AbstractSchedulerTask extends AbstractTask {


	/**
	 * @var \Tx_PtExtbase_Logger_Logger
	 */
	private $logger;


	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;


	/**
	 * This function is public because it has to be called in the test methods for preparation.
	 * The Initialization process can't be called:
	 * 	1. in constructor because the constructor won't be called on unseralize
	 * 	2. in the __wakeup method because the wakeup will be called before configurations are loaded
	 */
	public function initialize(){
		$this->initializeExtbase();
		$this->initializeObject();
	}


	/**
	 * Initialize Extbase
	 *
	 * This is necessary to resolve the TypoScript interface definitions
	 */
	protected function initializeExtbase() {
		$configuration['extensionName'] = $this->getExtensionName();
		$configuration['pluginName'] = 'dummy';

		$extbaseBootstrap = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Core\Bootstrap'); /** @var \TYPO3\CMS\Extbase\Core\Bootstrap $extbaseBootstrap */
		$extbaseBootstrap->initialize($configuration);

		$this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
	}


	/**
	 * @return void
	 */
	protected function initializeObject() {
	}


	/**
	 * Start a stopwatch
	 *
	 * @return integer Execution id
	 */
	public function markExecution() {
		TimeTracker::start('SchedulerTaskMeasure');
		$this->initialize();
		return parent::markExecution();
	}



	/**
	 * Removes given execution from list
	 *
	 * @param integer $executionID Id of the execution to remove.
	 * @param \Exception $failure An exception to signal a failed execution
	 *
	 * @return    void
	 */
	public function unmarkExecution($executionID, \Exception $failure = NULL) {
		$this->logToApplicationLog();
		parent::unmarkExecution($executionID, $failure);
	}



	/**
	 * Return the extensionName for Extbase Initialization
	 *
	 * @return string
	 */
	abstract public function getExtensionName();



	/**
	 * @return void
	 */
	protected function logToApplicationLog() {
		$this->logger = $this->objectManager->get('\Tx_PtExtbase_Logger_Logger');

		if ($this->logger instanceof \Tx_PtExtbase_Logger_Logger) {
			$usedTime = TimeTracker::stop('SchedulerTaskMeasure');
			$data['time'] = $usedTime;
			$taskTitle = trim($this->getTaskTitle());

			if ($taskTitle !== '') {
				$data['taskTitle'] = $taskTitle;
			}

			$this->logger->info(sprintf('Scheduler Task "%s" completed', $taskTitle, $usedTime), get_class($this), $data);
		}
	}
}