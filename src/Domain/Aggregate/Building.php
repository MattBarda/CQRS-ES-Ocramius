<?php

declare(strict_types=1);

namespace Building\Domain\Aggregate;

use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserCheckedIn;
use Building\Domain\DomainEvent\UserCheckedOut;
use mysql_xdevapi\Exception;
use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;

final class Building extends AggregateRoot
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var string
     */
    private $name;

    /**
     * @var null[] indexed by username
     */
    private $checkedInUsers = [];

    public static function new(string $name) : self
    {
        $self = new self();

        $self->recordThat(NewBuildingWasRegistered::occur(
            (string) Uuid::uuid4(),
            [
                'name' => $name
            ]
        ));

        return $self;
    }

    public function checkInUser(string $username)
    {
        if (array_key_exists($username, $this->checkedInUsers)) {
            throw new \DomainException("User {$username} has already checked in.");
        }

        $this->recordThat(UserCheckedIn::occur(
            $this->uuid->toString(),
            [
                'username' => $username
            ]
        ));
    }

    public function checkOutUser(string $username)
    {
        if (! array_key_exists($username, $this->checkedInUsers)) {
            throw new \DomainException("User {$username} hasn't checked in.");
        }

        $this->recordThat(UserCheckedOut::occur(
            $this->uuid->toString(),
            [
                'username' => $username
            ]
        ));
    }

    public function whenNewBuildingWasRegistered(NewBuildingWasRegistered $event)
    {
        $this->uuid = $event->uuid();
        $this->name = $event->name();
    }

    protected function whenUserCheckedIn(UserCheckedIn $event)
    {
        $this->checkedInUsers[$event->username()] = null;
    }

    protected function whenUserCheckedOut(UserCheckedOut $event)
    {
        unset($this->checkedInUsers[$event->username()]);
    }

    /**
     * {@inheritDoc}
     */
    protected function aggregateId() : string
    {
        return (string) $this->uuid;
    }

    /**
     * {@inheritDoc}
     */
    public function id() : string
    {
        return $this->aggregateId();
    }
}
