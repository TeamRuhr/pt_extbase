<?php
/***************************************************************
 *  Copyright (C) 2014 punkt.de GmbH
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

/**
 * Test case for class Tx_PtExtbase_Logger_Logger
 *
 * @package pt_extbase
 */
class Tx_PtExtbase_Tests_Functional_Logger_LoggerTest extends Tx_PtExtbase_Tests_Unit_AbstractBaseTestcase {

	/**
	 * @var string
	 */
	protected $proxyClass;


	/**
	 * @var string
	 */
	protected $logFilePath;


	/**
	 * @var Tx_PtDpppEsales_Logger_Logger
	 */
	protected $logger;


	/**
	 * @var string
	 */
	protected $logExceptionsPath;


	/**
	 * @return void
	 */
	public function setUp() {
		$this->logFilePath = __DIR__ . '/Logs/TestLog.log';
		$this->logExceptionsPath = __DIR__ . '/Logs/Exceptions/';

		Tx_PtExtbase_Utility_Files::createDirectoryRecursively($this->logExceptionsPath);

		$container = $this->objectManager->get('TYPO3\CMS\Extbase\Object\Container\Container'); /** @var \TYPO3\CMS\Extbase\Object\Container\Container $container */
		$container->registerImplementation('TYPO3\CMS\Core\Mail\MailMessage', 'Tx_PtTest_Mock_SwiftMessage');

		$this->proxyClass = $this->buildAccessibleProxy('Tx_PtExtbase_Logger_Logger');

		$this->getMockBuilder('Tx_PtExtbase_Logger_LoggerConfiguration')
			->setMockClassName('Tx_PtExtbase_Logger_LoggerConfigurationMock')
			->setMethods(array('getLogLevelThreshold', 'getEmailLogLevelThreshold', 'weHaveAnyEmailReceivers', 'getEmailReceivers'))
			->getMock();
		$loggerConfigurationMock = $this->objectManager->get('Tx_PtExtbase_Logger_LoggerConfigurationMock');
		$loggerConfigurationMock->expects($this->any())
			->method('getLogLevelThreshold')
			->will($this->returnValue(\TYPO3\CMS\Core\Log\LogLevel::DEBUG));
		$loggerConfigurationMock->expects($this->any())
			->method('getEmailLogLevelThreshold')
			->will($this->returnValue(\TYPO3\CMS\Core\Log\LogLevel::INFO));
		$loggerConfigurationMock->expects($this->any())
			->method('weHaveAnyEmailReceivers')
			->will($this->returnValue(TRUE));
		$loggerConfigurationMock->expects($this->any())
			->method('getEmailReceivers')
			->will($this->returnValue('ry28@hugo10.intern.punkt.de'));

		$container->registerImplementation('Tx_PtExtbase_Logger_LoggerConfiguration', 'Tx_PtExtbase_Logger_LoggerConfigurationMock');

		$this->getMockBuilder('Tx_PtExtbase_Utility_UserAgent')
				->setMethods(array('getUserAgentData'))
				->setMockClassName('Tx_PtExtbase_Utility_UserAgentMock')
				->getMock();
		$userAgentMock = $this->objectManager->get('Tx_PtExtbase_Utility_UserAgentMock');
		$userAgentMock->expects($this->any())
				->method('getUserAgentData')
				->will($this->returnValue('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36'));

		$container->registerImplementation('Tx_PtExtbase_Utility_UserAgent', 'Tx_PtExtbase_Utility_UserAgentMock');

		$this->getMockBuilder('Tx_PtExtbase_Utility_ServerInformation')
				->setMethods(array('getServerHostName'))
				->setMockClassName('ServerInformationMock')
				->getMock();
		$serverInformationMock = $this->objectManager->get('ServerInformationMock');
		$serverInformationMock->expects($this->any())
				->method('getServerHostName')
				->will($this->returnValue('spiderman.web.net'));

		$container = $this->objectManager->get('TYPO3\CMS\Extbase\Object\Container\Container'); /** @var \TYPO3\CMS\Extbase\Object\Container\Container $container */
		$container->registerImplementation('Tx_PtExtbase_Utility_ServerInformation', 'ServerInformationMock');

		$this->logger = $this->objectManager->get($this->proxyClass);
		$this->logger->configureLogger($this->logFilePath, $this->logExceptionsPath);
	}



	/**
	 * @throws Exception
	 * @return void
	 */
	public function tearDown() {
		unset($this->logger);
		file_put_contents($this->logFilePath, ''); // Clear File
		Tx_PtExtbase_Utility_Files::emptyDirectoryRecursively($this->logExceptionsPath);
	}



	/**
	 * @test
	 */
	public function logInfoWithoutFurtherParameter(){
		$this->logger->info('test');
		$this->assertLogFileContains('component="Tx.PtExtbase.Logger.Logger": test');
		$this->assertLogFileContains('[INFO]');
	}



	/**
	 * @test
	 */
	public function logInfoWithClassName(){
		$this->logger->info('test', __CLASS__);
		$this->assertLogFileContains('component="Tx.PtExtbase.Tests.Functional.Logger.LoggerTest": test');
		$this->assertLogFileContains('[INFO]');
	}



	/**
	 * @test
	 */
	public function logInfoWithClassNameAndAdditionlData(){
		$this->logger->info('test', __CLASS__, array('BLA'));
		$this->assertLogFileContains(' component="Tx.PtExtbase.Tests.Functional.Logger.LoggerTest": test - ["BLA"]');
		$this->assertLogFileContains('[INFO]');
	}



	/**
	 * @test
	 */
	public function logException() {

		try {
			throw new \Exception('This is a Test Exception');
		} catch(\Exception $e) {
			$this->logger->logException($e);
		}

		$this->assertLogFileContains('[CRITICAL]');
		$this->assertLogFileContains('component="Tx.PtExtbase.Logger.Logger": Uncaught exception: This is a Test Exception - See also:');

		$this->assertCount(1, Tx_PtExtbase_Utility_Files::readDirectoryRecursively($this->logExceptionsPath));
	}



	/**
	 * @param $expectedString
	 */
	protected function assertLogFileContains($expectedString) {
		if(!file_exists($this->logFilePath)) sleep(1);

		$this->assertFileExists($this->logFilePath);
		$data = file_get_contents($this->logFilePath);
		$this->assertNotEmpty($data);
		$this->assertContains($expectedString, $data, sprintf('Expected "%s" - But Log File is "%s"', $expectedString, $data));
	}



	/**
	 * @test
	 */
	public function loggerSendsEmailOnError() {
		$mailerMock = $this->objectManager->get('Tx_PtTest_Utility_Mailer'); /** @var Tx_PtTest_Utility_Mailer $mailerMock */
		$mailerMock->prepare();

		$this->logger->critical('The fantastic three', NULL, array('Summer', 'Sun', 'Sunshine'));

		$mail = $mailerMock->getFirstMail();

		$this->assertEquals(file_get_contents(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('pt_extbase') . 'Tests/Functional/Logger/ExpectedErrorMail.txt'), $mail->getBody());
	}


}