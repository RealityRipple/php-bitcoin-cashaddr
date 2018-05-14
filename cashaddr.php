<?php
 if (extension_loaded('gmp'))
  define('USE_EXT', 'GMP');
 else if(extension_loaded('bcmath') && !defined('USE_EXT'))
  define ('USE_EXT', 'BCMATH');
 else
  die('GMP or BC Math extensions required.');

 spl_autoload_register
 (
  function ($f)
  {
   $base = dirname(__FILE__)."/phpecc/";
   $interfaceFile = $base."classes/interface/".$f."Interface.php";
   if (file_exists($interfaceFile))
    require_once $interfaceFile;
   $classFile = $base."classes/".$f.".php";
   if (file_exists($classFile))
    require_once $classFile;
   $utilFile = $base."classes/util/".$f.".php";
   if (file_exists($utilFile))
    require_once $utilFile;
  }
 );

 require_once(dirname(__FILE__).'/bigint.php');

 function convertToCash($address)
 {
  $addr = AddrTools::base58check_decode($address);
  $addrV = ord($addr[0]);
  $addrP = substr($addr, 1);
  $sz = 0;
  switch (strlen($addrP))
  {
   case 20:
    $sz = 0;
    break;
   case 24:
    $sz = 1;
    break;
   case 28:
    $sz = 2;
    break;
   case 32:
    $sz = 3;
    break;
   case 40:
    $sz = 4;
    break;
   case 48:
    $sz = 5;
    break;
   case 56:
    $sz = 6;
    break;
   case 64:
    $sz = 7;
    break;
   default:
    return "Invalid Data Length: ".strlen($addrP);
  }
  $ver = 0;
  if ($addrV === 5)
   $type = 1;
  else
   $type = 0;
  $dec = chr(($ver << 7) | ($type << 3) | ($sz));
  $sum = AddrTools::checksum('bitcoincash', 0, chr($type << 3).$addrP, 0);
  return 'bitcoincash:'.AddrTools::base32_encode($dec.$addrP).AddrTools::base32_encode($sum);
 }

 function convertFromCash($address)
 {
  if (strpos($address, ':') === false)
   return $address;
  $pre = substr($address, 0,  strpos($address, ':'));
  $adr = substr($address, strpos($address, ':') + 1);
  $adh = substr($adr, -8);
  $adr = substr($adr, 0, -8);
  $dec = AddrTools::base32_decode($adr);
  $ver = ord($dec[0]);
  $v   = (($ver >> 7) & 0x01);
  $t   = (($ver >> 3) & 0x15);
  $s   = ($ver & 0x07);
  switch ($s)
  {
   case 0:
    $sz = 20;
    break;
   case 1:
    $sz = 24;
    break;
   case 2:
    $sz = 28;
    break;
   case 3:
    $sz = 32;
    break;
   case 4:
    $sz = 40;
    break;
   case 5:
    $sz = 48;
    break;
   case 6:
    $sz = 56;
    break;
   case 7:
    $sz = 64;
    break;
   default:
    $sz = $s;
  }
  $hash = substr($dec, 1, $sz);
  $sum  = AddrTools::base32_decode($adh);
  $vsm  = AddrTools::checksum($pre, 0, chr($ver).$hash, 0);
  if ($sum !== $vsm)
   return "Invalid Checksum";
  return AddrTools::pubKeyStr($hash, $t);
 }

 class AddrTools
 {
  public static function convBase($numberInput, $fromBaseInput, $toBaseInput)
  {
   if ($fromBaseInput == $toBaseInput)
    return $numberInput;
   $fromBase = str_split($fromBaseInput,1);
   $toBase = str_split($toBaseInput,1);
   $number = str_split($numberInput,1);
   $fromLen=strlen($fromBaseInput);
   $toLen=strlen($toBaseInput);
   $numberLen=strlen($numberInput);
   $retval='';
   if ($toBaseInput == '0123456789')
   {
    $retval = 0;
    for ($i = 1;$i <= $numberLen; $i++)
     $retval = bcadd($retval, bcmul(array_search($number[$i-1], $fromBase),bcpow($fromLen,$numberLen-$i)));
    return $retval;
   }
   if ($fromBaseInput != '0123456789')
    $base10 = self::convBase($numberInput, $fromBaseInput, '0123456789');
   else
    $base10 = $numberInput;
   if ($base10<strlen($toBaseInput))
    return $toBase[$base10];
   while ($base10 != '0')
   {
    $retval = $toBase[bcmod($base10,$toLen)].$retval;
    $base10 = bcdiv($base10,$toLen,0);
   }
   return $retval;
  }

  public static function base58check_decode($str)
  {
   $dictionary = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
   $sV = ltrim(strtr($str, $dictionary, '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuv'), '0');
   if (USE_EXT == 'GMP')
    $v = gmp_init($sV, 58);
   else
    $v = self::convBase($sV, '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuv', '0123456789');
   $v = BigInt::big2bin($v);
   for ($i = 0; $i < strlen($str); $i++)
   {
    if ($str[$i] != $dictionary[0])
     break;
    $v = "\x00" . $v;
   }
   $checksum = substr($v, -4);
   $v = substr($v, 0, -4);
   $expCheckSum = substr(hash('sha256', hash('sha256', $v, true), true), 0, 4);
   if ($expCheckSum != $checksum)
    return 'Checksum Mismatch';
   return $v;
  }

  public static function base58_encode($bin)
  {
   $dictionary = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
   $n = BigInt::bin2big($bin);
   $r = '';
   while (BigInt::cmp($n, 58) >= 0)
   {
    list($div, $mod) = BigInt::div_qr($n, 58);
    $r = $dictionary[$mod].$r;
    $n = $div;
   }
   if ($n !== 0)
    $r = $dictionary[$n].$r;
   for ($i = 0; $i < strlen($bin); $i++)
   {
    if (ord($bin[$i]) !== 0)
     break;
    $r = $dictionary[0].$r;
   }
   return $r;
  }

  public static function checksum($prefix, $sep, $addr, $sum)
  {
   $raw = self::lower5($prefix);
   $raw.= chr($sep);
   $raw.= self::split5($addr);
   for ($i = 0; $i < 8; $i++)
    $raw.= chr($sum);
   $ret = self::polyMod($raw);
   $bin = decbin($ret);
   while (strlen($bin) < 40)
    $bin = '0'.$bin;
   return self::bin2text($bin);
  }

  public static function lower5($text)
  {
   $raw = '';
   for ($i = 0; $i < strlen($text); $i++)
   {
    $bin = decbin(ord($text[$i]) & 0x1F);
    while (strlen($bin) < 8)
     $bin = '0'.$bin;
    $raw.= $bin;
   }
   return self::bin2text($raw);
  }

  public static function split5($data)
  {
   $bin = self::text2bin($data);
   while ((strlen($bin) % 5) != 0)
    $bin.= '0';
   $ret = '';
   while (strlen($bin) > 0)
   {
    $chunk = substr($bin, 0, 5);
    $bin = substr($bin, 5);
    $ret.= chr(bindec($chunk));
   }
   return $ret;
  }

  public static function join5($five)
  {
   $bin = self::array2bin($five);
   $raw = '';
   while (strlen($bin) > 0)
   {
    $chunk = substr($bin, 0, 8);
    $bin = substr($bin, 8);
    $raw.= substr($chunk, 3);
   }
   while ((strlen($raw) % 8) != 0)
    $raw = substr($raw, 0, -1);
   return self::bin2text($raw);
  }

  public static function bin2text($bin)
  {
   $ret = '';
   while(strlen($bin) >= 8)
   {
    $seg = substr($bin, 0, 8);
    $bin = substr($bin, 8);
    $ret.= chr(bindec($seg));
   }
   if (strlen($bin) > 0)
   {
    $ret.= chr(bindec($bin));
   }
   return $ret;
  }

  public static function text2bin($in)
  {
   $raw = '';
   for ($i = 0; $i < strlen($in); $i++)
   {
    $bin = decbin(ord($in[$i]));
    while (strlen($bin) < 8)
     $bin = '0'.$bin;
    $raw.= $bin;
   }
   return $raw;
  }

  public static function array2bin($in)
  {
   $raw = '';
   for ($i = 0; $i < count($in); $i++)
   {
    $bin = decbin($in[$i]);
    while (strlen($bin) < 8)
     $bin = '0'.$bin;
    $raw.= $bin;
   }
   return $raw;
  }

  public static function polyMod($v)
  {
   $c = 1;
   for($i = 0; $i < strlen($v); $i++)
   {
    $d = ord($v[$i]);
    $c0 = $c >> 35;
    $c = (($c & 0x07FFFFFFFF) << 5) ^ $d;
    if (($c0 & 0x01) == 0x01)
     $c ^= 0x98f2bc8e61;
    if (($c0 & 0x02) == 0x02)
     $c ^= 0x79b76d99e2;
    if (($c0 & 0x04) == 0x04)
     $c ^= 0xf33e5fb3c4;
    if (($c0 & 0x08) == 0x08)
     $c ^= 0xae2eabe2a8;
    if (($c0 & 0x10) == 0x10)
     $c ^= 0x1e4f43e470;
   }
   return $c ^ 1;
  }

  public static function base32_encode($bin)
  {
   $dictionary = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';
   $s = self::split5($bin);
   $r = '';
   for ($i = 0; $i < strlen($s); $i++)
   {
    $o = ord($s[$i]);
    $r.= $dictionary[$o];
   }
   return $r;
  }

  public static function base32_decode($str)
  {
   $dictionary = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';
   $s = array();
   for ($i = 0; $i < strlen($str); $i++)
   {
    $s[] = strpos($dictionary, $str[$i]);
   }
   return self::join5($s);
  }

  public static function pubKeyStr($bin, $type)
  {
   $e = '';
   if ($type === 1)
    $cType = 5;
   else
    $cType = 0;
   if ($cType > 0xFFFF)
    $e = pack('N', $cType);
   else if ($cType > 0xFF)
    $e = pack('n', $cType);
   else
    $e = chr($cType);
   $e.= $bin;
   $t = hash('sha256', hash('sha256', $e, true), true);
   $n = $e.substr($t, 0, 4);
   return self::base58_encode($n);
  }

 }
