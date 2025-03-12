<?php

namespace App\Enums;

enum StoreStatus: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
    case LUNCH_BREAK = 'lunch_break';
} 