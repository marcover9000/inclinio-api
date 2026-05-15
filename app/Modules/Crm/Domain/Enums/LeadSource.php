<?php

namespace App\Modules\Crm\Domain\Enums;

enum LeadSource: string
{
    case WebForm = 'web_form';
    case Manual = 'manual';
}
