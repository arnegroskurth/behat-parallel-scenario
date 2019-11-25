<?php

namespace Tonic\Behat\ParallelScenarioExtension\ScenarioInfo;

use PHPUnit\Framework\TestCase;

/**
 * Class ScenarioInfoTest.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ScenarioInfoTest extends TestCase
{
    /**
     * @see ScenarioInfo::__toString
     */
    public function testToString()
    {
        $scenarioInfo = new ScenarioInfo('file', '80');
        $this->assertEquals('file:80', (string) $scenarioInfo);
    }
}
