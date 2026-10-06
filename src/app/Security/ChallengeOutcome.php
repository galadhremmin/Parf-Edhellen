<?php

namespace App\Security;

enum ChallengeOutcome
{
    case Passed;
    case Wrong;
    case Exhausted; // too many wrong codes; the challenge is closed
    case Expired;   // no open challenge in this session, or it timed out
}
