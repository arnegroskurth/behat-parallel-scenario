<?php

namespace Tonic\Behat\ParallelScenarioExtension\Feature;

use Behat\Gherkin\Node\FeatureNode;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfo;
use Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfoExtractor;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcess;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcessFactory;
use Tonic\ParallelProcessRunner\ParallelProcessRunner;

/**
 * Class FeatureRunnerTest.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class FeatureRunnerTest extends TestCase
{
    /**
     * @return array
     */
    public function providerSetMaxParallelProcess()
    {
        return [
            [1],
            [2],
        ];
    }

    /**
     * @param int $maxParallelProcess
     *
     * @dataProvider providerSetMaxParallelProcess
     */
    public function testSetMaxParallelProcess($maxParallelProcess)
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $scenarioInfoExtractor = $this->createMock(ScenarioInfoExtractor::class);
        $scenarioProcessFactory = $this->createMock(ScenarioProcessFactory::class);
        $parallelProcessRunner = $this->createMock(ParallelProcessRunner::class);

        $parallelProcessRunner->expects($this->once())->method('setMaxParallelProcess')->with($maxParallelProcess);

        /** @var $eventDispatcher EventDispatcherInterface $featureRunner */
        /** @var $scenarioInfoExtractor ScenarioInfoExtractor $featureRunner */
        /** @var $scenarioProcessFactory ScenarioProcessFactory $featureRunner */
        /** @var $parallelProcessRunner ParallelProcessRunner $featureRunner */
        $featureRunner = new FeatureRunner(
            $eventDispatcher,
            $scenarioInfoExtractor,
            $scenarioProcessFactory,
            $parallelProcessRunner
        );

        $featureRunner->setMaxParallelProcess($maxParallelProcess);
    }

    /**
     * @return array
     */
    public function providerRun()
    {
        return [
            'empty' => [
                [],
            ],
            '1 scenario in 1 group' => [
                [
                    [
                        new ScenarioInfo('file', 1),
                    ],
                ],
            ],
            '2 scenario in 1 group' => [
                [
                    [
                        new ScenarioInfo('file', 1),
                        new ScenarioInfo('file', 2),
                    ],
                ],
            ],
            '2 scenario in 2 group' => [
                [
                    [
                        new ScenarioInfo('file', 1),
                    ],
                    [
                        new ScenarioInfo('file', 1),
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $scenarioGroups
     *
     * @dataProvider providerRun
     */
    public function testRun(array $scenarioGroups)
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $scenarioInfoExtractor = $this->createConfiguredMock(ScenarioInfoExtractor::class, ['extract' => $scenarioGroups]);
        $scenarioInfoExtractor->expects($this->once())->method('extract');

        $scenarioProcessFactory = $this->createMock(ScenarioProcessFactory::class);

        $index = 0;
        $processGroups = [];
        foreach ($scenarioGroups as $groupId => $scenarios) {
            $processGroups[$groupId] = [];
            foreach ($scenarios as $scenarioInfo) {
                $process = $this->createMock(ScenarioProcess::class);
                $processGroups[$groupId][] = $process;

                $scenarioProcessFactory->expects($this->at($index))->method('make')->with($scenarioInfo)->willReturn($process);
                $index++;
            }
        }
        $scenarioProcessFactory->expects($this->exactly($index))->method('make');

        $parallelProcessRunner = $this->createMock(ParallelProcessRunner::class);

        $parallelProcessRunner->expects($this->exactly(count($processGroups)))->method('reset')->willReturn($parallelProcessRunner);
        foreach ($processGroups as $index => $processes) {
            // count index with methods amount correction
            $parallelProcessRunner->expects($this->at($index * 3 + 1))->method('add')->with($processes)->willReturn($parallelProcessRunner);
        }

        $parallelProcessRunner->expects($this->exactly(count($processGroups)))->method('add');
        $parallelProcessRunner->expects($this->exactly(count($processGroups)))->method('run')->willReturn($parallelProcessRunner);

        $featureNode = $this->createMock(FeatureNode::class);

        /** @var EventDispatcherInterface $eventDispatcher */
        /** @var ScenarioInfoExtractor $scenarioInfoExtractor */
        /** @var ScenarioProcessFactory $scenarioProcessFactory */
        /** @var ParallelProcessRunner $parallelProcessRunner */
        /** @var FeatureNode $featureNode */
        $featureRunner = new FeatureRunner(
            $eventDispatcher,
            $scenarioInfoExtractor,
            $scenarioProcessFactory,
            $parallelProcessRunner
        );

        $featureRunner->run($featureNode);
    }
}
