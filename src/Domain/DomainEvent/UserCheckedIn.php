<?php

namespace Building\Domain\DomainEvent;

use Prooph\EventSourcing\AggregateChanged;

class UserCheckedIn extends AggregateChanged
{
    public function username()
    {
        return $this->payload()['username'];
    }
}
