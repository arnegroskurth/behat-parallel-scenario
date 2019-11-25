<?php

namespace Tonic\Behat\ParallelScenarioExtension\Listener;

use PHPUnit\Framework\TestCase;
use Tonic\Behat\ParallelScenarioExtension\Event\ParallelScenarioEventType;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcess;
use Tonic\ParallelProcessRunner\Event\ProcessEvent;
use Tonic\ParallelProcessRunner\ParallelProcessRunner;

/**
 * Class StopOnFailureTest.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class StopOnFailureTest extends TestCase
{
    /**
     * @see StopOnFailure::getSubscribedEvents
     */
    public function testGetSubscribedEvents()
    {
        $this->assertEquals([
            ParallelScenarioEventType::PROCESS_AFTER_STOP => 'stopOnFailure',
        ], StopOnFailure::getSubscribedEvents());
    }

    /**
     * @see StopOnFailure::stopOnFailure
     */
    public function testStopOnFailureWithError()
    {
        $parallelProcessRunner = $this->createMock(ParallelProcessRunner::class);
        $parallelProcessRunner->expects($this->once())->method('stop');

        $process = $this->createMock(ScenarioProcess::class);
        $process->expects($this->once())->method('withError')->willReturn(true);

        $event = new ProcessEvent($process);

        $listener = $this->getMockBuilder(StopOnFailure::class)
            ->onlyMethods(['terminate'])
            ->setConstructorArgs([$parallelProcessRunner])
            ->getMock();
        $listener->expects($this->once())->method('terminate')->with(1);

        /** @var StopOnFailure $listener */
        /** @var ProcessEvent $event */
        $listener->stopOnFailure($event);
    }

    /**
     * @see StopOnFailure::stopOnFailure
     */
    public function testStopOnFailureWithoutError()
    {
        $parallelProcessRunner = $this->createMock(ParallelProcessRunner::class);
        $parallelProcessRunner->expects($this->never())->method('stop');

        $process = $this->createMock(ScenarioProcess::class);
        $process->expects($this->once())->method('withError')->willReturn(false);

        $event = new ProcessEvent($process);

        $listener = $this->getMockBuilder(StopOnFailure::class)
            ->onlyMethods(['terminate'])
            ->setConstructorArgs([$parallelProcessRunner])
            ->getMock();
        $listener->expects($this->never())->method('terminate');

        /** @var StopOnFailure $listener */
        /** @var ProcessEvent $event */
        $listener->stopOnFailure($event);
    }
}
