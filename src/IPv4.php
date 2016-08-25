<?php
namespace IPTools;

/**
 * @author Safarov Alisher <alisher.safarov@outlook.com>
 * @link https://github.com/S1lentium/IPTools
 */
class IPv4 extends IP
{	
	/**
	 * @return string
	 */
	public function getVersion()
	{		
		return self::IP_V4;
	}

	/**
	 * @return int
	 */
	public function getMaxPrefixLength()
	{
		return self::IP_V4_MAX_PREFIX_LENGTH;
	}

	/**
	 * @return int
	 */
	public function getOctetsCount()
	{
		return self::IP_V4_OCTETS;
	}

	/**
	 * @return string
	 */
	public function getReversePointer()
	{
		$reverseOctets = array_reverse(explode('.', $this->__toString()));

		return implode('.', $reverseOctets) . '.in-addr.arpa';
	}

	/**
	 * @return string
	 */
	public function toLong()
	{		
		return sprintf('%u', ip2long(inet_ntop($this->in_addr)));
	}

}
