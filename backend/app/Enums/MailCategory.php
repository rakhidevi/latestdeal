<?php

namespace App\Enums;

enum MailCategory: string
{
    case Security = 'security';
    case Transactional = 'transactional';
    case Notification = 'notification';
    case Marketing = 'marketing';
}
