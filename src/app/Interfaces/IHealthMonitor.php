<?php

namespace App\Interfaces;

interface IHealthMonitor
{
    function testOnce(): ?\Exception;
}
