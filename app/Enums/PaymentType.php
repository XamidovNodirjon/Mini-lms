<?php

namespace App\Enums;

enum PaymentType: string
{
    case Debt = 'debt';
    case Balance = 'balance';
}
