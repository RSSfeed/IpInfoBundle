<?php
namespace ITF\IpInfoBundle\Components;

use Symfony\Component\Intl\Intl;

class IpDetails
{
	private $ip;
	private $hostname;
	private $city;
	private $region;
	private $country;
	private $loc;
	private $lat;
	private $lng;
	private $org;
	private $details;

	public function __construct()
	{
		$this->setDetails();
	}

	public function getClientIP($exclude_localhost = true)
	{
		$ipaddress = NULL;
		if (getenv('HTTP_CLIENT_IP'))
			$ipaddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			$ipaddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
			$ipaddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			$ipaddress = getenv('REMOTE_ADDR');
		else
			$ipaddress = 'UNKNOWN';

		if (!$exclude_localhost ||
			($exclude_localhost && preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $ipaddress))) {

			return $ipaddress;
		}

		return false;
	}

	public function getUrlContent($url)
	{
		if (function_exists('curl_init')){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$output = curl_exec($ch);
			curl_close($ch);
		} else {
			$output = file_get_contents($url);
		}

		return $output;
	}

	public function getDetails($ip = NULL)
	{
		if (empty($ip)) {
			$ip = $this->getClientIP();
		}

		// request
		$response = $this->getUrlContent('http://ipinfo.io/' . $ip);
		$json = json_decode($response);

		// init ipdetails
		return $json;
	}

	protected function setDetails($details = array())
	{
		if (empty($details)) $details = $this->getDetails();

		$this->details = $details;

		if (count($details) > 0) {
			foreach ($details as $key => $value) {
				if (property_exists($this, $key)) {
					$this->{$key} = $value;
				}
			}
		}
	}

	public function setIp($ip)
	{
		$details = $this->getDetails($ip);
		$this->setDetails($details);

		return $this;
	}

	public function resetIp()
	{
		$details = $this->getDetails();
		$this->setDetails($details);

		return $this;
	}

	public function getIp()
	{
		return $this->ip;
	}

	public function getHostname()
	{
		return $this->hostname;
	}

	public function getCity()
	{
		return $this->city;
	}

	public function getRegion()
	{
		return $this->region;
	}

	public function getCountry()
	{
		return $this->country;
	}

	public function getCountryName($locale)
	{
		\Locale::setDefault('en');
		return Intl::getRegionBundle()->getCountryName($this->getCountry(), $locale);
	}

	public function getLocation()
	{
		return $this->loc;
	}

	protected function setLatLng()
	{
		list($this->lat, $this->lng) = explode(',', $this->getLocation());
	}

	public function getLatitude()
	{
		if (empty($this->lat)) {
			$this->setLatLng();
		}

		return $this->lat;
	}

	public function getLongitude()
	{
		if (empty($this->lng)) {
			$this->setLatLng();
		}

		return $this->lng;
	}

	public function getOrganization()
	{
		return $this->org;
	}

	public function getAll()
	{
		return $this->details;
	}
}