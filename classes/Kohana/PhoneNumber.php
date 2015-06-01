<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_PhoneNumber
{
	protected $number;
	protected $config;
	protected static $instance;

	protected $phoneNumberUtil;
	protected $shortNumberInfo;
	protected $phoneNumberGeocoder;

	protected $phoneNumber;
	protected $phoneNumberRegion = NULL;
	protected $phoneNumberType = NULL;
	protected $validNumber = FALSE;
	protected $validNumberForRegion = FALSE;
	protected $possibleNumber = FALSE;
	protected $isPossibleNumberWithReason = NULL;
	protected $geolocation = NULL;
	protected $phoneNumberToCarrierInfo = NULL;
	protected $timezone = NULL;

	protected $countryCodeSource = array(
		0 => 'FROM_NUMBER_WITH_PLUS_SIGN',
		1 => 'FROM_NUMBER_WITH_IDD',
		2 => 'FROM_NUMBER_WITHOUT_PLUS_SIGN',
		3 => 'FROM_DEFAULT_COUNTRY'
	);

	protected $possibleNumberReason = array(
		0 => 'IS_POSSIBLE',
		1 => 'INVALID_COUNTRY_CODE',
		2 => 'TOO_SHORT',
		3 => 'TOO_LONG'
	);

	protected $phoneNumberTypes = array(
		0 => 'FIXED_LINE',
		1 => 'MOBILE',
		2 => 'FIXED_LINE_OR_MOBILE',
		3 => 'TOLL_FREE',
		4 => 'PREMIUM_RATE',
		5 => 'SHARED_COST',
		6 => 'VOIP',
		7 => 'PERSONAL_NUMBER',
		8 => 'PAGER',
		9 => 'UAN',
		10 => 'UNKNOWN',
		27 => 'EMERGENCY',
		28 => 'VOICEMAIL',
		29 => 'SHORT_CODE',
		30 => 'STANDARD_RATE'
	);

	protected $expectedCost = array(
		3 => 'TOLL_FREE',
		4 => 'PREMIUM_RATE',
		30 => 'STANDARD_RATE',
		10 => 'UNKNOWN_COST'
	);

	protected $expectedCostForRegion = array(
		3 => 'TOLL_FREE',
		4 => 'PREMIUM_RATE',
		30 => 'STANDARD_RATE',
		10 => 'UNKNOWN_COST'
	);

    public static function instance( $number, array $config = NULL )
    {
		if ( ! isset(self::$instance))
			self::$instance = new self($number, $config);

		return self::$instance;
    }

    public function __construct( $number, array $config = NULL )
    {
		$this->number = $number;

		if (empty($this->number))
			throw new Kohana_Exception('Phone number is required');

		$this->config = Kohana::$config->load('phonenumber')->as_array();

		if ( ! empty($config))
			$this->config = Arr::merge($this->config, $config);

		if ( ! isset($this->config['country']))
			$this->config['country'] = '';

		if (empty($this->config['country']))
			throw new Kohana_Exception('Country code is required');

		$this->config['country'] = strtoupper($this->config['country']);

		if ( ! isset($this->config['language']))
			$this->config['language'] = '';

		if ( ! isset($this->config['region']))
			$this->config['region'] = '';

		$this->config['language'] = empty($this->config['language']) ? 'en' : strtolower($this->config['language']);
		$this->config['region'] = empty($this->config['region']) ? NULL : strtoupper($this->config['region']);


		$this->phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
		$this->shortNumberInfo = \libphonenumber\ShortNumberInfo::getInstance();
		$this->phoneNumberGeocoder = \libphonenumber\geocoding\PhoneNumberOfflineGeocoder::getInstance();

		try {
			$this->phoneNumber = $this->phoneNumberUtil->parse($number, $this->config['country'], NULL, TRUE);
			$this->possibleNumber = $this->phoneNumberUtil->isPossibleNumber($this->phoneNumber);
			$this->isPossibleNumberWithReason = $this->phoneNumberUtil->isPossibleNumberWithReason($this->phoneNumber);
			$this->validNumber = $this->phoneNumberUtil->isValidNumber($this->phoneNumber);
			$this->validNumberForRegion = $this->phoneNumberUtil->isValidNumberForRegion($this->phoneNumber, $input['country']);
			$this->phoneNumberRegion = $this->phoneNumberUtil->getRegionCodeForNumber($this->phoneNumber);
			$this->phoneNumberType = $this->phoneNumberUtil->getNumberType($this->phoneNumber);

			$this->geolocation = $this->phoneNumberGeocoder->getDescriptionForNumber(
				$this->phoneNumber,
				$this->config['language'],
				$this->config['region']
			);

			$this->phoneNumberToCarrierInfo = \libphonenumber\PhoneNumberToCarrierMapper::getInstance()->getNameForNumber(
				$this->phoneNumber,
				$this->config['language']
			);

			$this->timezone = \libphonenumber\PhoneNumberToTimeZonesMapper::getInstance()->getTimeZonesForNumber($this->phoneNumber);
		} catch (\libphonenumber\NumberParseException $e) {
			throw new Kohana_Exception(
				$e->getMessage(),
				NULL,
				$e->getCode()
			);
		}
	}

	public function getPNObject()
	{
		return $this->phoneNumber;
	}

	public function getPNUtilInstance()
	{
		return $this->phoneNumberUtil;
	}

	public function getShortNumberInstance()
	{
		return $this->shortNumberInfo;
	}

	public function getPNGeocoderInstance()
	{
		return $this->phoneNumberGeocoder;
	}

	public function getCountryCodeSource()
	{
		return $this->countryCodeSource[$this->phoneNumber->getCountryCodeSource()];
	}

	public function formatE164()
	{
		return $this->phoneNumberUtil->format($this->phoneNumber, \libphonenumber\PhoneNumberFormat::E164);
	}

	public function formatNational()
	{
		return $this->phoneNumberUtil->format($this->phoneNumber, \libphonenumber\PhoneNumberFormat::NATIONAL);
	}

	public function formatInternational()
	{
		return $this->phoneNumberUtil->format($this->phoneNumber, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
	}

	public function formatRFC3966()
	{
		return $this->phoneNumberUtil->format($this->phoneNumber, \libphonenumber\PhoneNumberFormat::RFC3966);
	}

	public function formatInOriginalFormat()
	{
		return $this->phoneNumberUtil->formatInOriginalFormat($this->phoneNumber, $this->config['country']);
	}

	public function formatOutOfCountryCallingNumber($country)
	{
		$country = strtoupper($country);

		return $this->phoneNumberUtil->formatOutOfCountryCallingNumber($this->phoneNumber, $country);
	}

	public function isPossibleShortNumber()
	{
		return $this->shortNumberInfo->isPossibleShortNumber($this->phoneNumber);
	}

	public function isValidShortNumber()
	{
		return $this->shortNumberInfo->isValidShortNumber($this->phoneNumber);
	}

	public function isPossibleShortNumberForRegion()
	{
		return $this->shortNumberInfo->isPossibleShortNumberForRegion($this->phoneNumber, $this->phoneNumberRegion);
	}

	public function isValidShortNumberForRegion()
	{
		return $this->shortNumberInfo->isValidShortNumberForRegion($this->phoneNumber, $this->phoneNumberRegion);
	}

	public function getExpectedCost()
	{
		return $this->expectedCost[$this->shortNumberInfo->getExpectedCost($this->phoneNumber)];
	}

	public function getExpectedCostForRegion()
	{
		return $this->expectedCostForRegion[$this->shortNumberInfo->getExpectedCostForRegion($this->phoneNumber, $this->phoneNumberRegion)];
	}

	public function isEmergencyNumber()
	{
		return $this->shortNumberInfo->isEmergencyNumber($this->number, $this->config['country']);
	}

	public function connectsToEmergencyNumber()
	{
		return $this->shortNumberInfo->connectsToEmergencyNumber($this->number, $this->config['country']);
	}

	public function getExampleNumber()
	{
		return $this->phoneNumberUtil->getExampleNumber($this->config['country']);
	}

	public function getExampleNumberForType()
	{
		return $this->phoneNumberUtil->getExampleNumberForType($this->config['country'], $this->phoneNumberType);
	}

	public function getExampleShortNumber()
	{
		return $this->phoneNumberUtil->getExampleShortNumber($this->config['country']);
	}

	public function getGeolocation()
	{
		return $this->geolocation;
	}

	public function getCarrier()
	{
		return $this->phoneNumberToCarrierInfo;
	}

	public function getTimeZones()
	{
		if (is_array($this->timezone))
			return implode($this->timezone, ', ');
		else
			return $this->timezone;
	}

	public function isPossibleNumber()
	{
		return $this->possibleNumber;
	}

	public function isPossibleNumberWithReason()
	{
		return $this->possibleNumberReason[$this->isPossibleNumberWithReason];
	}

	public function isValidNumber()
	{
		return $this->validNumber;
	}

	public function isValidNumberForRegion()
	{
		return $this->validNumberForRegion;
	}

	public function getRegionCodeForNumber()
	{
		return $this->phoneNumberRegion;
	}

	public function getNumberType()
	{
		return $this->phoneNumberTypes[$this->phoneNumberType];
	}
}