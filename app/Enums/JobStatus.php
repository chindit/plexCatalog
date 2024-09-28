<?php

namespace App\Enums;


enum JobStatus: string
{
	case created = 'created';
	case processing = 'processing';
	case completed = 'completed';
	case failed = 'failed';
}
