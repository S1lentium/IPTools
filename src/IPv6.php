<?php
namespace IPTools;

/**
 * @author Safarov Alisher <alisher.safarov@outlook.com>
 * @link https://github.com/S1lentium/IPTools
 */
class IPv6 extends IP
{	
	/**
	 * @return string
	 */
	public function getVersion()
	{		
		return self::IP_V6;
	}

	/**
	 * @return int
	 */
	public function getMaxPrefixLength()
	{
		return self::IP_V6_MAX_PREFIX_LENGTH;
	}

	/**
	 * @return int
	 */
	public function getOctetsCount()
	{
		return self::IP_V6_OCTETS;
	}

	/**
	 * @return string
	 */
	public function getReversePointer()
	{
		$unpacked = unpack('H*hex', $this->in_addr);
		$reverseOctets = array_reverse(str_split($unpacked['hex']));

		return implode('.', $reverseOctets) . '.ip6.arpa';
	}

	/**
	 * @return string
	 */
	public function toLong()
	{
		$octet = self::IP_V6_OCTETS - 1;
		$long = 0;
		
		foreach ($chars = unpack('C*', $this->in_addr) as $char) {
			$long = bcadd($long, bcmul($char, bcpow(256, $octet--)));
		}
			
		return $long;
	}

}
