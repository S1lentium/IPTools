<?php
namespace IPTools;

/**
 * @author Safarov Alisher <alisher.safarov@outlook.com>
 * @link https://github.com/S1lentium/IPTools
 */
abstract class IP
{
	const IP_V4 = 4;
	const IP_V6 = 6;

	const IP_V4_MAX_PREFIX_LENGTH = 32;
	const IP_V6_MAX_PREFIX_LENGTH = 128;

	const IP_V4_OCTETS = 4;
	const IP_V6_OCTETS = 16;

	/**
	 * @var string
	 */
	protected $in_addr;

	use PropertyTrait;

	private static function getClassName($ip)
	{
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			return __NAMESPACE__ . '\IPv4';
		} elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			return __NAMESPACE__ . '\IPv6';
		}

		throw new \Exception("Invalid IP address format");		
	}

		/**
	 * @return int
	 */
	abstract public function getVersion();

	/**
	 * @return int
	 */
	abstract public function getMaxPrefixLength();

	/**
	 * @return int
	 */
	abstract public function getOctetsCount();

	/**
	 * @return string
	 */
	abstract public function getReversePointer();	

	/**
	 * @param string ip
	 * @throws \Exception
	 */
	public function __construct($ip)
	{
		if (!filter_var($ip, FILTER_VALIDATE_IP)) {
			throw new \Exception("Invalid IP address format");
		}
		$this->in_addr = inet_pton($ip);
	}	

	/**
	 * @return string
	 */
	public function __toString()
	{
		return inet_ntop($this->in_addr);
	}	

	/**
	 * @param string ip
	 * @return IPv4|IPv6
	 */
	public static function parse($ip)
	{
		if (strpos($ip, '0x') === 0) {
			$instance = self::parseHex(substr($ip, 2));
		} elseif (strpos($ip, '0b') === 0) {
			$instance = self::parseBin(substr($ip, 2));
		} else if (is_numeric($ip)) {
			$instance = self::parseLong($ip);
		} else {
			$className = self::getClassName($ip);
			$instance = new $className($ip);
		}

		return $instance;
	}

	/**
	 * @param string $binIP
	 * @throws \Exception
	 * @return IP
	 */
	public static function parseBin($binIP)
	{
		if (!preg_match('/^([0-1]{32}|[0-1]{128})$/', $binIP)) {
			throw new \Exception("Invalid binary IP address format");
		}

		$in_addr = '';
		foreach (array_map('bindec', str_split($binIP, 8)) as $char) {
			$in_addr .= pack('C*', $char);
		}

		$ip = inet_ntop($in_addr);
		$className = self::getClassName($ip);

		return new $className($ip);
	}

	/**
	 * @param string $hexIP
	 * @throws \Exception
	 * @return IP
	 */
	public static function parseHex($hexIP)
	{
		if (!preg_match('/^([0-9a-fA-F]{8}|[0-9a-fA-F]{32})$/', $hexIP)) {
			throw new \Exception("Invalid hexadecimal IP address format");
		}

		$ip = inet_ntop(pack('H*', $hexIP));
		$className = self::getClassName($ip);

		return new $className($ip);
	}

	/**
	 * @param string|int $longIP
	 * @param int $version
	 * @return IPv4|IPv6
	 */
	public static function parseLong($longIP, $version=self::IP_V4) 
	{
		if ($version === self::IP_V4) {
			$ip = long2ip((float)$longIP);
		} elseif($version === self::IP_V6) {
			$binary = array();
			for ($i = 0; $i < self::IP_V6_OCTETS; $i++) {
				$binary[] = bcmod($longIP, 256);
				$longIP = bcdiv($longIP, 256, 0);
			}
			$ip = inet_ntop(call_user_func_array('pack', array_merge(array('C*'), array_reverse($binary))));
		} else {
			throw new \Exception("Wrong IP version");
		}

		$className = self::getClassName($ip);

		return new $className($ip);
	}	

	/**
	 * @param string $inAddr
	 * @return IP
	 */
	public static function parseInAddr($inAddr)
	{
		$ip = inet_ntop($inAddr);
		$className = self::getClassName($ip);

		return new $className($ip);
	}

	/**
	 * @return string
	 */
	public function inAddr()
	{
		return $this->in_addr;
	}

	/**
	 * @return string
	 */
	public function toBin()
	{
		$binary = array();
		foreach (unpack('C*', $this->in_addr) as $char) {
			$binary[] = str_pad(decbin($char), 8, '0', STR_PAD_LEFT);
		}

		return implode($binary);
	}

	/**
	 * @return string
	 */
	public function toHex()
	{
		return bin2hex($this->in_addr);
	}

	/**
	 * @return string
	 */
	abstract public function toLong();

	/**
	 * @param int $to
	 * @return IP
	 * @throws \Exception
	 */
	public function next($to=1)
	{
		if ($to < 0) {
			throw new \Exception("Number must be greater than 0");
		}

		$unpacked = unpack('C*', $this->in_addr);

		for ($i = 0; $i < $to; $i++)	{
			for ($byte = count($unpacked); $byte >= 0; --$byte) {
				if ($unpacked[$byte] < 255) {
					$unpacked[$byte]++;
					break;
				} else {
					$unpacked[$byte] = 0;
				}
			}
		}

		$ip = inet_ntop(call_user_func_array('pack', array_merge(array('C*'), $unpacked)));

		return new static($ip);
	}

	/**
	 * @param int $to
	 * @return IP
	 * @throws \Exception
	 */
	public function prev($to=1)
	{

		if ($to < 0) {
			throw new \Exception("Number must be greater than 0");
		}

		$unpacked = unpack('C*', $this->in_addr);

		for ($i = 0; $i < $to; $i++)	{
			for ($byte = count($unpacked); $byte >= 0; --$byte) {
				if ($unpacked[$byte] == 0) {
					$unpacked[$byte] = 255;
				} else {
					$unpacked[$byte]--;
					break;
				}
			}
		}

		$ip = inet_ntop(call_user_func_array('pack', array_merge(array('C*'), $unpacked)));

		return new static($ip);
	}

}
