<?php
namespace ITF\IpInfoBundle\Components;

use ITF\IpInfoBundle\Exception\RateLimitExceed;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class IpDetails
{
	/** @var ContainerInterface */
	private $container;

	/** @var array */
	private $config = array();

	public function __construct(ContainerInterface $containerInterface)
	{
		$this->container = $containerInterface;
		$this->config = $this->container->getParameter('ip_info');
	}

	/**
	 * @return Request
	 */
	private function getRequest()
	{
		return $this->container->get('request');
	}
	
	/**
	 * Get all request data from headers
	 * 
	 * @return array
	 */
	public function getRequestDetails()
	{
		return array(
			'ip' => $this->getClientIP(),
			'hostname' => $this->getHostname()
		);
	}
	
	/**
	 * Get client ip address
	 * 
	 * @param bool $exclude_localhost
	 * @return bool|null
	 */
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
			($exclude_localhost && $this->isLocalhost($ipaddress))) {

			return $ipaddress;
		}

		return false;
	}
	
	/**
	 * Check if ip is localhost
	 * 
	 * @param null $ip
	 * @return bool
	 */
	public function isLocalhost($ip = null)
	{
		if ($ip === null) $ip = $this->getClientIP();

		return preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $ip);
	}
	
	/**
	 * Get hostname
	 * 
	 * @return string
	 */
	public function getHostname()
	{
		return $this->getRequest()->server->get('SERVER_NAME');
	}
	
	/**
	 * Get request of ipinfo
	 * 
	 * @param null $ip
	 *
	 * @return array
	 * @throws MissingOptionsException|RateLimitExceed
	 */
	public function requestIpInfo($ip = NULL)
	{
		if ($this->config['access_key'] === NULL) {
			throw new MissingOptionsException(sprintf("Access key must be set to retrieve ip info"));
		}

		if (empty($ip)) {
			$ip = $this->getClientIP();
		}

		$response = $this->getUrlContent('http://ipinfo.io/' . $ip);

		if (preg_match('/Rate\ limit\ exceeded/', $response)) {
			throw new RateLimitExceed($response);
		}

		$json = json_decode($response);

		// init ipdetails
		return $json;
	}
	
	
	/**
	 * Get url content
	 * 
	 * @param $url
	 * @return mixed
	 */
	private function getUrlContent($url)
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
}