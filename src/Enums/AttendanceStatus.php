<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Late = 'late';
    case Absent = 'absent';
    case Partial = 'partial';
    case Excused = 'excused';
}
