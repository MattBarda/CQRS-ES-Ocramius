<?php

namespace Building\Domain\DomainEvent;

use Prooph\EventSourcing\AggregateChanged;

class UserCheckedOut extends AggregateChanged
{
    public function username()
    {
        return $this->payload()['username'];
    }
}
