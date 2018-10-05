#!/usr/bin/env php
<?php

namespace PunktDe\PtExtbase\Tests\Functional\Utility\Lock;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Daniel Lienert <lienert@punkt.de>
 *
 *
 *  All rights reserved
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

class MySqlLockTestSecondInstance
{
    /**
     * @var \PDO
     */
    protected $mySQLConnection;


    protected function connect($context)
    {

        // Load system specific configuration for Apache mode
        $GLOBALS['TYPO3_CONF_VARS'] = require(__DIR__ . '/../../../../../../LocalConfiguration.php');
        $dpppConfiguration = __DIR__ . '/../../../../../../configurations/' . $context . '/AdditionalConfiguration.php';

        if (file_exists($dpppConfiguration)) {
            @include($dpppConfiguration);
        }

        $credentials = $GLOBALS['TYPO3_CONF_VARS']['DB'];

        $this->mySQLConnection = new \PDO('mysql:host=' . $credentials['Connections']['Default']['host'] . ';dbname=' . $credentials['Connections']['Default']['dbname'], $credentials['Connections']['Default']['user'], $credentials['Connections']['Default']['password']);
    }


    public function test()
    {
        if (!isset($_SERVER['argv']['1']) || !isset($_SERVER['argv']['2']) || !isset($_SERVER['argv']['3'])) {
            throw new \Exception('You have to specify the context, lock identifier and the testType', 1428853716);
        }

        $context = $_SERVER['argv']['1'];
        $lockIdentifier = $_SERVER['argv']['2'];
        $testType = $_SERVER['argv']['3'];
        $timeout = isset($_SERVER['argv']['4']) ? $_SERVER['argv']['4'] : 0;

        $this->connect($context);

        switch ($testType) {
            case 'acquireExclusiveLock':
                $this->testAcquireExclusiveLock($lockIdentifier, $timeout);
                break;
            case 'freeLock':
                $this->freeLock($lockIdentifier);
                break;
            case 'testIfLockIsFree':
                $this->testIfLockIsFree($lockIdentifier);
                    break;
            default:
                throw new \Exception('No testMethod defined for ' . $testType);
        }
    }

    public function testIfLockIsFree($lockIndentifier)
    {
        $lockResult = $this->mySQLConnection->query(sprintf('SELECT IS_FREE_LOCK("%s") as res', $lockIndentifier))->fetch();
        echo $lockResult['res'];
    }

    public function testAcquireExclusiveLock($lockIdentifier, $timeout)
    {
        $lockResult = $this->mySQLConnection->query(sprintf('SELECT GET_LOCK("%s", %d) as res', $lockIdentifier, $timeout))->fetch();
        echo $lockResult['res'];
    }

    public function freeLock($lockIdentifier)
    {
        $res = $this->mySQLConnection->query(sprintf('SELECT FREE_LOCK("%s") as res', $lockIdentifier));
        if ($res) {
            $lockResult = $res->fetch();
            echo $lockResult['res'];
        }
        echo 0;
    }
}

$secondInstance = new MySqlLockTestSecondInstance();
$secondInstance->test();
