<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Enums;

enum RecordingStatus: string
{
    case Pending = 'pending';
    case Recording = 'recording';
    case Processing = 'processing';
    case Ready = 'ready';
    case Failed = 'failed';
}
