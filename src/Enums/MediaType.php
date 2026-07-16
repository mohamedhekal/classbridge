<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Enums;

enum MediaType: string
{
    case Audio = 'audio';
    case Video = 'video';
    case Screen = 'screen';
}
