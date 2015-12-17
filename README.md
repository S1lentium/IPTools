# IPTools

PHP Library for manipulating network addresses (IPv4 and IPv6).

[![Build Status](https://travis-ci.org/S1lentium/IPTools.svg)](https://travis-ci.org/S1lentium/IPTools)
[![Coverage Status](https://coveralls.io/repos/S1lentium/IPTools/badge.svg?branch=master&service=github)](https://coveralls.io/github/S1lentium/IPTools?branch=master)
[![Code Climate](https://codeclimate.com/github/S1lentium/IPTools/badges/gpa.svg)](https://codeclimate.com/github/S1lentium/IPTools)

## Installation
Composer:
```json
{
    "require": {
        "s1lentium/iptools": "*"
    }
}
```

## Usage

### IP Operations
```php
$ip = new IP('192.168.1.1');
echo $ip->version;// IPv4
```

```php
$ip = new IP('fc00::');
echo $ip->version; // IPv6
```

**Parsing IP from integer, binary and hex representation:**
```php
echo (string)IP::parse(2130706433); // 127.0.0.1
echo (string)IP::parse('0b11000000101010000000000100000001') // 192.168.1.1
echo (string)IP::parse('0x0a000001'); // 10.0.0.1
```
or:
```php
echo (string)IP::parseLong(2130706433); // 127.0.0.1
echo (string)IP::parseBin('11000000101010000000000100000001'); // 192.168.1.1
echo (string)IP::parseHex('0a000001'); // 10.0.0.1
```

**Converting IP to other formats:**
```php
echo IP::parse('192.168.1.1')->bin // 11000000101010000000000100000001
echo IP::parse('10.0.0.1')->hex // 0a000001
echo IP::parse('127.0.0.1')->long // 2130706433
```

#### Other public properties:

`maxPrefixLength`
The max number of bits in the address representation: 32 for IPv4, 128 for IPv6.

`octetsCount`
The count of octets in IP address: 4 for IPv4, 16 for IPv6

`reversePointer`
The name of the reverse DNS PTR for the address:
```php
echo new IP::parse('192.0.2.5')->reversePointer // 5.2.0.192.in-addr.arpa
echo new IP::parse('2001:db8::567:89ab')->reversePointer // b.a.9.8.7.6.5.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa
```

### Networking
```php
$network = new Network(new IP('192.168.1.1'), new IP('255.255.255.0'));
print_r($network->info);
```
	Array
	(
	    [IP] => 192.168.1.1
	    [netmask] => 255.255.255.0
	    [network] => 192.168.1.0
	    [prefixLength] => 24
	    [CIDR] => 192.168.1.0/24
	    [wildcard] => 0.0.0.255
	    [broadcast] => 192.168.1.255
	    [firstIP] => 192.168.1.0
	    [lastIP] => 192.168.1.255
	    [blockSize] => 256
	    [firstHost] => 192.168.1.1
	    [lastHost] => 192.168.1.254
	    [hostsCount] => 254
	)


```php
echo Network::parse('192.0.0.1 255.0.0.0')->CIDR; // 192.0.0.0/8
echo (string)Network::parse('192.0.0.1/8')->netmask; // 255.0.0.0
echo (string)Network::parse('192.0.0.1'); // 192.0.0.1/32
```

**Iterate over Network`s Host-IPs:**
```php
$network = Network::parse('192.168.1.0/24');
foreach($network as $ip) {
	echo (string)$ip . '<br>';
}
```
	192.168.1.1
	...
	192.168.1.254
	


**Excluding IP from Network:**
```php
$excluded = Network::parse('192.0.0.0/8')->exclude(new IP('192.168.1.1'));
foreach($excluded as $network) {
	echo (string)$network . '<br>';
}
```
	192.0.0.0/9
	192.128.0.0/11
	192.160.0.0/13
	192.168.0.0/24
	192.168.1.0/32
	192.168.1.2/31
	...
	192.192.0.0/10


**Excluding Subnet from Network:**
```php
$excluded = Network::parse('192.0.0.0/8')->exclude(new Network('192.168.1.0/24'));
foreach($excluded as $network) {
	echo (string)$network . '<br>';
}
```
	192.0.0.0/9
	192.128.0.0/11
	192.160.0.0/13
	192.168.0.0/24
	192.168.2.0/23
	...
	192.192.0.0/10


**Count of Host-IPs**
```php
echo count(Network::parse('192.168.1.0/24')) // 254
```

### Range Operations
**Defining a range in different formats:**
```php
$range = new Range(new IP('192.168.1.0'), new IP('192.168.1.255'));
$range = Range::parse('192.168.1.0-192.168.1.255');
$range = Range::parse('192.168.1.*');
$range = Range::parse('192.168.1.0/24');
```
**Check if IP is within Range:**
```php
echo Range::parse('192.168.1.1-192.168.1.254')->contains(new IP('192.168.1.5')); // true
echo Range::parse('::1-::ffff')->contains(new IP('::1234')); // true
```

**Iterating over Range IPs:**
```php
$range = Range::parse('192.168.1.1-192.168.1.254');
foreach($range as $ip) {
	echo (string)$ip . '<br>';
}
```
	192.168.1.1
	...
	192.168.1.254


**Get Networks that fit into a specified range of IPs:**
```php
$networks = Range::parse('192.168.1.1-192.168.1.254')->getNetworks();

foreach($networks as $network) {
	echo (string)$network . '<br>';
}
```
	192.168.1.1/32
	192.168.1.2/31
	192.168.1.4/30
	192.168.1.8/29
	192.168.1.16/28
	192.168.1.32/27
	192.168.1.64/26
	192.168.1.128/26
	192.168.1.192/27
	192.168.1.224/28
	192.168.1.240/29
	192.168.1.248/30
	192.168.1.252/31
	192.168.1.254/32


**Count of IPs in Range**
```php
echo count(Range::parse('192.168.1.1-192.168.1.254')) // 254
```

# License
The library is released under the [MIT](https://opensource.org/licenses/MIT).
