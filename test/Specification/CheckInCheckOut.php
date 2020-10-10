<?php

namespace Specification;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Behat\Behat\Context\Context;
use Building\Domain\Aggregate\Building;
use Building\Domain\DomainEvent\CheckInAnomalyDetected;
use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserCheckedIn;
use Exception;
use Prooph\EventSourcing\AggregateChanged;
use Rhumsaa\Uuid\Uuid;

final class CheckInCheckOut implements Context
{
    /**
     * @var Uuid
     */
    private $buildingId;

    /**
     * @var AggregateChanged[]
     */
    private $pastEvents = [];

    /**
     * @var Building|null
     */
    private $building;

    public function __construct()
    {
        $this->buildingId = Uuid::uuid4()->toString();
    }

    /**
     * @Given a building was registered
     */
    public function aBuildingWasRegistered()
    {
        $this->pastEvents[] = NewBuildingWasRegistered::occur(
            $this->buildingId,
            ['name' => 'The Building name']
        );
    }

    /**
     * @Given a user checked into the building
     */
    public function aUserCheckedIntoThBuilding()
    {
        $this->pastEvents[] = UserCheckedIn::occur(
            $this->buildingId,
            ['username' => 'matt']
        );
    }

    /**
     * @When the user checks into the building
     */
    public function theUserChecksIntoTheBuilding()
    {
        $this->getOrCreateBuilding()->checkInUser(
            'matt'
        );
    }

    /**
     * @Then the user should have been checked into the building
     * @throws Exception
     * @throws AssertionFailedException
     */
    public function theUserShouldHaveBeenCheckedIntoTheBuilding()
    {
        $recordedEvents = $this->getRecordedEvents();

        Assertion::count($recordedEvents, 1);
        Assertion::isInstanceOf($recordedEvents[0], UserCheckedIn::class);
    }

    /**
     * @Then a check in anomaly should have been detected
     * @throws Exception
     * @throws AssertionFailedException
     */
    public function aCheckInAnomalyShouldHaveBeenDetected()
    {
        $recordedEvents = $this->getRecordedEvents();

        Assertion::count($recordedEvents, 2);
        Assertion::isInstanceOf($recordedEvents[0], UserCheckedIn::class);
        Assertion::isInstanceOf($recordedEvents[1], CheckInAnomalyDetected::class);
    }

    private function getOrCreateBuilding() : Building
    {
        return $this->building
            ?? $this->building = (function (array $events) : Building {
                return Building::reconstituteFromHistory(new \ArrayIterator($events));
            })
                ->bindTo(null, Building::class)
                ->__invoke($this->pastEvents);
    }

    /**
     * @return AggregateChanged[]
     *
     * @throws \ReflectionException
     */
    private function getRecordedEvents() : array
    {
        $getEvents = new \ReflectionMethod(
            Building::class,
            'popRecordedEvents'
        );

        $getEvents->setAccessible(true);

        return $getEvents->invoke($this->getOrCreateBuilding());
    }
}