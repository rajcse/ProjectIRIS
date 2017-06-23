# Project IRIS

Iris is a Facebook Messenger bot that could send SMS messages to any Philippine-based mobile number. For a demo, visit [the Iris Facebook Page](https://web.facebook.com/ProjectIrisMessenger/).

> The demo might not work as of the moment because the app is still pending review by Facebook. To request a demo, send a message on the page asking for tester access so I can add you to the Testers group.

## Deployment Requirements

* A Facebook Page
* Facebook App Credentials (get one at [Facebook Developers](https://developers.facebook.com/))
* A web hosting servce with PHP & SSL support ([NodeComet](https://nodecomet.com)* offers this for $3 per year!)
* A Chikka API account with credits

> **FULL DISCLOSURE:** I own and operate NodeComet. Supporting NodeComet is appreciated but you can also get your hosting elsewhere.

## Instructions

Upload the files to your HTTPS-capable web hosting service, and create `config.php`. Use the following template:

```
<?php

$facebook = [
    'app_id'    =>  'YOUR-APP-ID',
    'app_secret'    =>  'YOUR-APP-SECRET',
    'page_access_token' =>  'YOUR-PAGE-ACCESS-TOKEN', //
    'verify_token'  =>  'YOUR-APP-VERIFICATION-TOKEN'
];

$chikka = [
    'id'    =>  'YOUR-CHIKKA-API-ID',
    'secret'    =>  'YOUR-CHIKKA-API-SECRET-KEY',
    'shortcode' =>  'YOUR-CHIKKA-API-SHORTCODE'
];
```

Your **page access token** can be retrieved from your Developer Dashboard > Your App > Products > Messenger > Token Generation. Then run `composer install` to grab the dependencies.

## License

This bot is licensed under the MIT Open Source License.

```
Copyright 2017 Liam Demafelix

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```