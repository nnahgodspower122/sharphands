<?php

namespace App\Enums;

enum WorkerStatus: string
{
    case Available = 'available';
    case Busy = 'busy';
}
