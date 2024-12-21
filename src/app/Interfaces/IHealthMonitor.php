<?php

namespace App\Interfaces;

interface IHealthMonitor
{
    public function testOnce(): ?\Exception;
}
