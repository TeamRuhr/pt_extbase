<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2011 punkt.de GmbH - Karlsruhe, Germany - http://www.punkt.de
 *  Authors: Daniel Lienert, Michael Knoll, Christoph Ehscheidt
 *  All rights reserved
 *
 *
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Unit tests for session persistence manager
 *
 * @package Tests
 * @subpackage State\Session
 * @author Michael Knoll 
 * @author Daniel Lienert
 * @see Tx_PtExtbase_State_Session_SessionPersistenceManager
 *
 */
class Tx_PtExtbase_Tests_Unit_State_Session_SessionPersistenceManagerTest extends \PunktDe\PtExtbase\Testing\Unit\AbstractBaseTestcase
{
    /** @test */
    public function classExists()
    {
        $sessionAdapterMock = $this->getMockBuilder(Tx_PtExtbase_State_Session_Storage_AdapterInterface::class)
            ->getMock(); /* @var $sessionAdapterMock Tx_PtExtbase_State_Session_Storage_AdapterInterface */
        $sessionPersistenceManager = new Tx_PtExtbase_State_Session_SessionPersistenceManager($sessionAdapterMock);
        $this->assertTrue(is_a($sessionPersistenceManager, 'Tx_PtExtbase_State_Session_SessionPersistenceManager'));
    }
    
    /** @test */
    public function reloadFromSessionKeepsObjectValues()
    {
        $persistableObjectStub = new Tx_PtExtbase_Tests_Unit_State_Stubs_PersistableObject();
        $sessionPersistenceManager = new Tx_PtExtbase_State_Session_SessionPersistenceManager(new Tx_PtExtbase_Tests_Unit_State_Stubs_SessionAdapterMock());
        $persistableObjectStub->initSomeData();
        $sessionPersistenceManager->persistToSession($persistableObjectStub);
        
        $reloadedPersistableObject = new Tx_PtExtbase_Tests_Unit_State_Stubs_PersistableObject();
        $sessionPersistenceManager->loadFromSession($reloadedPersistableObject);
        $this->assertSame('testvalue1', $reloadedPersistableObject->dummyData['testkey1'], 'Reloaded data in persistable object was not the same as the expected data!');
    }

    /** @test */
    public function getSessionDataByNamespaceReturnsCorrectValue()
    {
        $sessionAdapterMock = new Tx_PtExtbase_Tests_Unit_State_Stubs_SessionAdapterMock();
        
        $sessionPersistenceManager = new Tx_PtExtbase_State_Session_SessionPersistenceManager($sessionAdapterMock);
        $sessionPersistenceManager->init();
        
        $this->assertEquals($sessionPersistenceManager->getSessionDataByNamespace('test1.test2.test3'), 'value');
    }

    /** @test */
    public function getSessionDataHashReturnsExpectedHash()
    {
        $this->markTestSkipped('Geht nicht');
        $sessionPersistenceManager = $this->getAccessibleMock('Tx_PtExtbase_State_Session_SessionPersistenceManager', ['dummyMethod'], [new Tx_PtExtbase_Tests_Unit_State_Stubs_SessionAdapterMock()]);
        $sessionPersistenceManager->_set('sessionData', ['test']);
        $hash = $sessionPersistenceManager->getSessionDataHash();
        $this->assertEquals(md5(serialize(['test'])), $hash);
    }
}
