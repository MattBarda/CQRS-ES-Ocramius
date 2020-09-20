<?php

namespace Building\Domain\Command;

use Prooph\Common\Messaging\Command;
use Rhumsaa\Uuid\Uuid;

class CheckOutUser extends Command
{
    /**
     * @var Uuid
     */
    private $buildingId;
    /**
     * @var string
     */
    private $username;

    private function __construct(Uuid $buildingId, string $username)
    {
        $this->init();

        $this->buildingId = $buildingId;
        $this->username = $username;
    }

    public static function fromBuildingAndUsername(Uuid $buildingId, string $username) : self
    {
        return new self(
            $buildingId,
            $username
        );
    }

    public function username() : string
    {
        return $this->username;
    }

    public function buildingId() : string
    {
        return $this->buildingId->toString();
    }

    /**
     * {@inheritDoc}
     */
    public function payload(): array
    {
        return [
            'buildingId' => $this->buildingId->toString(),
            'username' => $this->username,
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function setPayload(array $payload)
    {
        $this->buildingId = Uuid::fromString($payload['buildingId']);
        $this->username = $payload['username'];
    }
}
