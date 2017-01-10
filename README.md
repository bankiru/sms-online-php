# sms-inline SMS
PHP binding for sms-online.ru SMS gateway

## Installation

```sh
composer require bankiru/sms-online
```

## Usage

### Standalone

```php
<?php

use Bankiru\Sms\SmsOnline\SmsOnline;
use Bankiru\Sms\SmsOnline\SmsOnlineTransport;
use ScayTrase\SmsDeliveryBundle\Service\ShortMessageInterface;

class MySms implements  ShortMessageInterface {
    /* ... */
}

$transport = new SmsOnlineTransport(
    new SmsOnline(new \GuzzleHttp\Client(), 'user', 'pass', 'https://service.qtelecom.ru/public/http/'),
    'friendly_man',
    new \Psr\Log\NullLogger(),
    '_transaction_prefix'
);
$transport->send(new MySms('1234567890', 'message body'));
```

### Symfony

Register bundle to the kernel

```php
class AppKernel extends Kernel {
    public function registerBundles() {
        return [
            //...
            new \ScayTrase\SmsDeliveryBundle\SmsDeliveryBundle();
            new \Bankiru\Sms\QtSms\QtSmsBundle(),
            //...
        ];
    }
}
```

Configure the sender

```yaml
sms_delivery:
  transport: sms_online.transport

sms_online:
    login: user
    password: pass
    url: https://bulk.sms-online.com/
    sender: friendly_man
    transaction_prefix: _transaction_prefx_
```

Send SMS

```php
class MyController extends Controller {
    public function sendAction() {
        $this->get('sms_delivery.sender')->spoolMessage(new MySms('1234567890', 'message body'));
    }
}
```
