<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Michael Knoll <knoll@punkt.de>
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


/**
 * Class implements testcase for the ifValueChanges ViewHelper.
 *
 * @author Michael Knoll <knoll@punkt.de>
 * @package Tests
 * @subpackage Unit\ViewHelpers
 * @see Tx_PtExtbase_ViewHelpers_IfValueChangesViewHelper
 */
class Tx_PtExtbase_Tests_Unit_ViewHelpers_IfValueChangesViewHelperTest extends \PunktDe\PtExtbase\Testing\Unit\AbstractBaseTestcase
{
    /** @test */
    public function renderCallsExpectedSubpartsInSingleValueMode()
    {
        $ifValueChangesViewHelper =
            $this->getMockBuilder(\PunktDe\PtExtbase\ViewHelpers\IfValueChangesViewHelper::class)
                ->setMethods(['renderThenChild', 'renderElseChild'])
                ->getMock();
        $ifValueChangesViewHelper->expects($this->at(0))->method('renderThenChild');
        $ifValueChangesViewHelper->expects($this->at(1))->method('renderElseChild');
        $ifValueChangesViewHelper->expects($this->at(2))->method('renderThenChild');
        $ifValueChangesViewHelper->expects($this->at(3))->method('renderElseChild');

        $values = ['first', 'first', 'second', 'second'];

        foreach ($values as $value) {
            /** @var \PunktDe\PtExtbase\ViewHelpers\IfValueChangesViewHelper $ifValueChangesViewHelper */
            $ifValueChangesViewHelper->setArguments(['value' => $value, 'key' => null]);
            $ifValueChangesViewHelper->render();
        }
    }


    /** @test */
    public function renderCallsExpectedSubpartsInMultiValueMode()
    {
        $ifValueChangesViewHelper =
            $this->getMockBuilder(\PunktDe\PtExtbase\ViewHelpers\IfValueChangesViewHelper::class)
                ->setMethods(['renderThenChild', 'renderElseChild'])
                ->getMock();

        $ifValueChangesViewHelper->expects($this->at(0))->method('renderThenChild');
        $ifValueChangesViewHelper->expects($this->at(1))->method('renderElseChild');
        $ifValueChangesViewHelper->expects($this->at(2))->method('renderThenChild');
        $ifValueChangesViewHelper->expects($this->at(3))->method('renderElseChild');
        $ifValueChangesViewHelper->expects($this->at(4))->method('renderThenChild');
        /* @var \PunktDe\PtExtbase\ViewHelpers\IfValueChangesViewHelper $ifValueChangesViewHelper  */
        $ifValueChangesViewHelper->setArguments(['value' => '1-1', 'key' => 'outer']);
        $ifValueChangesViewHelper->render('1-1', 'outer');
        $ifValueChangesViewHelper->setArguments(['value' => '1-1', 'key' => 'outer']);
        $ifValueChangesViewHelper->render('1-1', 'outer');
        $ifValueChangesViewHelper->setArguments(['value' => '1-1', 'key' => 'inner']);
        $ifValueChangesViewHelper->render('1-1', 'inner');
        $ifValueChangesViewHelper->setArguments(['value' => '1-2', 'key' => 'inner']);
        $ifValueChangesViewHelper->render('1-2', 'inner');
        $ifValueChangesViewHelper->setArguments(['value' => '1-2', 'key' => 'outer']);
        $ifValueChangesViewHelper->render('1-2', 'outer');
    }
}
