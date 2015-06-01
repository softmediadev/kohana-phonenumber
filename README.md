## libphonenumber for Kohana

#### Installation

Place all files in your modules directory.

Copy `MODPATH.menu/config/phonenumber.php` into `APPPATH/config/phonenumber.php` and customize.

Activate the module in `bootstrap.php`.

```php
<?php
Kohana::modules(array(
	...
	'phonenumber' => MODPATH.'phonenumber',
));
```
We create an instance of the class
```php
$pninst = PhoneNumber::instance('415 599 2671');
```
We can override the default configuration by passing a second parameter:
```php
$config = array(
	'country'	=> 'PA',
	'language'	=> 'es',
	'region'	=> 'PA'
);

$pninst = PhoneNumber::instance('415 599 2671', $config);
```

#### PhoneNumber Object
```php
echo $pninst->getPNObject()->getCountryCode();
// Output: 1
	
echo $pninst->getPNObject()->getNationalNumber();
// Output: 4155992671

echo $pninst->getPNObject()->isItalianLeadingZero();
// Output: FALSE

echo $pninst->getPNObject()->getRawInput();
// Output: 415 599 2671

echo $pninst->getPNObject()->getCountryCodeSource();
// Output: 3

echo $pninst->getCountryCodeSource();
// Output: FROM_DEFAULT_COUNTRY
```

#### Validation Results
```php
echo $pninst->isPossibleNumber();
// Output: TRUE

echo $pninst->isValidNumber();
// Output: TRUE

echo $pninst->isValidNumberForRegion();
// Output: TRUE

echo $pninst->getRegionCodeForNumber();
// Output: US

echo $pninst->getNumberType();
// Output: FIXED_LINE_OR_MOBILE
```

#### Formatting
```php
echo $pninst->formatE164();
// Output: +14155992671

echo $pninst->formatInOriginalFormat();
// Output: (415) 599-2671

echo $pninst->formatNational();
// Output: (415) 599-2671

echo $pninst->formatInternational();
// Output: +1 415-599-2671

echo $pninst->formatRFC3966();
// Output: tel:+1-415-599-2671

echo $pninst->formatOutOfCountryCallingNumber('US');
// Output: 1 (415) 599-2671

echo $pninst->formatOutOfCountryCallingNumber('CH');
// Output: 00 1 415-599-2671

echo $pninst->formatOutOfCountryCallingNumber('FR');
// Output: 00 1 415-599-2671
```

#### ShortNumberInfo
```php
echo $pninst->isPossibleShortNumber();
// Output: FALSE

echo $pninst->isValidShortNumber();
// Output: FALSE

echo $pninst->isPossibleShortNumberForRegion();
// Output: FALSE

echo $pninst->isValidShortNumberForRegion();
// Output: FALSE

echo $pninst->getExpectedCost();
// Output: UNKNOWN_COST

echo $pninst->getExpectedCostForRegion();
// Output: UNKNOWN_COST

echo $pninst->isEmergencyNumber();
// Output: FALSE

echo $pninst->connectsToEmergencyNumber();
// Output: FALSE
```

#### Example Numbers
```php
echo $pninst->getExampleNumber();
// Output: +12015555555

echo $pninst->getExampleNumberForType();
// Output: +12015555555

echo $pninst->getExampleShortNumber();
// Output: 911
```

#### PhoneNumber Geocoder
```php
echo $pninst->getGeolocation();
// Output: California
```

#### PhoneNumber Carrier
```php
echo $pninst->getCarrier();
// Output: 
```

#### PhoneNumber TimeZones
```php
echo $pninst->getTimeZones();
// Output: America/Los_Angeles
```

#### ABOUT AND LICENSE

Copyright (c) 2015, Soft Media Development. All right reserved. Website: www.smd.com.pa

This project is using the classes from https://github.com/giggsey/libphonenumber-for-php created by [giggsey](https://github.com/giggsey).

This project is made under BSD license. See LICENSE file for more information.
