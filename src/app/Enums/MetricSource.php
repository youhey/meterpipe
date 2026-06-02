<?php

namespace App\Enums;

enum MetricSource: string
{
    case OpenAi = 'openai';
    case LaravelCloud = 'laravel_cloud';
    case Digestpipe = 'digestpipe';
    case Radiopipe = 'radiopipe';
    case Voicepipe = 'voicepipe';
    case Playpipe = 'playpipe';
    case Meterpipe = 'meterpipe';
    case Manual = 'manual';
}
