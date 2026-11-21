<?php

namespace App\Device\Dto;

class DeviceInformation
{
    public function __construct(
        public readonly string $kernalName
    )
    {
    }

}
