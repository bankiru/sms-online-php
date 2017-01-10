<?php

namespace Bankiru\Sms\SmsOnline;

interface PrefixedSmsInterface
{
    /** @return string */
    public function getTransactionPrefix();
}
