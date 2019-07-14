<?php
// Crypto Class Library V.1.2 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class Crypto
{
	protected $KEY;
	protected $IV;
	protected $LastError;
	protected $PRVKEY;
	protected $PUBKEY;
	
	public function __construct($CryptConfig="")
	{
		if(is_array($CryptConfig))
		{
			$this->KEY = $CryptConfig['KEY'];
			$this->IV = $CryptConfig['IV'];
			$this->PRVKEY = $CryptConfig['PRVKEY'];
			$this->PUBKEY = $CryptConfig['PUBKEY'];
		}
	}

	public function OldDecrypt($Cypher,$KEY=NULL,$IV=NULL,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		if(!isset($IV) or empty($IV))
			$IV = $this->IV;
		try{
			$KEY = base64_decode($KEY);
			$IV = base64_decode($IV);
			$Cypher = base64_decode($Cypher);
			if($IsCompress)
			{
				$Cypher = gzuncompress($Cypher);
				if($Cypher === false)
					throw new Exception('bad compression');
			}
			return $this->PKCS7_UnPadding((mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $KEY, $Cypher, MCRYPT_MODE_CBC, $IV)));
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function MyEncrypt($PlainText,$Mode,$KEY=NULL,$IV=NULL,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		if(!isset($IV) or empty($IV))
			$IV = $this->IV;
		if(!isset($Mode) or empty($Mode))
			$Mode = $this->Mode;
		try{
			$KEY = base64_decode($KEY);
			$IV = base64_decode($IV);
			if($IsCompress)
			{
				$Cypher = openssl_encrypt($PlainText, $Mode, $KEY, OPENSSL_RAW_DATA, $IV);
				$Cypher = gzcompress($Cypher,9);
				if($Cypher === false)
					throw new Exception('bad compression');
				return base64_encode($Cypher);
			}
			else
			{
				return base64_encode(openssl_encrypt($PlainText, $Mode, $KEY, OPENSSL_RAW_DATA, $IV));
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function MyDecrypt($Cypher,$Mode,$KEY=NULL,$IV=NULL,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		if(!isset($IV) or empty($IV))
			$IV = $this->IV;
		if(!isset($Mode) or empty($Mode))
			$Mode = $this->Mode;
		try{
			$KEY = base64_decode($KEY);
			$IV = base64_decode($IV);
			$Cypher = base64_decode($Cypher);
			if($IsCompress)
			{
				$Cypher = gzuncompress($Cypher);
				if($Cypher === false)
					throw new Exception('bad compression');
			}
			return openssl_decrypt($Cypher, $Mode, $KEY, OPENSSL_RAW_DATA, $IV);
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function AdvDecrypt($Cypher,$KEY=NULL,$IV=NULL,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		if(!isset($IV) or empty($IV))
			$IV = $this->IV;
		try{
			$KEY = base64_decode($KEY);
			$IV = base64_decode($IV);
			$Cypher = base64_decode($Cypher);
			if($IsCompress)
			{
				$Cypher = gzuncompress($Cypher);
				if($Cypher === false)
					throw new Exception('bad compression');
			}
			return openssl_decrypt($Cypher, 'AES-128-CBC', $KEY, OPENSSL_RAW_DATA, $IV);
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function HighDecrypt($Cypher,$KEY=NULL,$IV=NULL,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		if(!isset($IV) or empty($IV))
			$IV = $this->IV;
		try{
			$KEY = base64_decode($KEY);
			$IV = base64_decode($IV);
			$Cypher = base64_decode($Cypher);
			if($IsCompress)
			{
				$Cypher = gzuncompress($Cypher);
				if($Cypher === false)
					throw new Exception('bad compression');
			}
			return openssl_decrypt($Cypher, 'AES-256-CBC', $KEY, OPENSSL_RAW_DATA, $IV);
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function AdvEncrypt($PlainText,$KEY=NULL,$IV=NULL,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		if(!isset($IV) or empty($IV))
			$IV = $this->IV;
		try{
			$KEY = base64_decode($KEY);
			$IV = base64_decode($IV);
			if($IsCompress)
			{
				$Cypher = openssl_encrypt($PlainText, 'AES-128-CBC', $KEY, OPENSSL_RAW_DATA, $IV);
				$Cypher = gzcompress($Cypher,9);
				if($Cypher === false)
					throw new Exception('bad compression');
				return base64_encode($Cypher);
			}
			else
			{
				return base64_encode(openssl_encrypt($PlainText, 'AES-128-CBC', $KEY, OPENSSL_RAW_DATA, $IV));
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function HighEncrypt($PlainText,$KEY=NULL,$IV=NULL,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		if(!isset($IV) or empty($IV))
			$IV = $this->IV;
		try{
			$KEY = base64_decode($KEY);
			$IV = base64_decode($IV);
			if($IsCompress)
			{
				$Cypher = openssl_encrypt($PlainText, 'AES-256-CBC', $KEY, OPENSSL_RAW_DATA, $IV);
				$Cypher = gzcompress($Cypher,9);
				if($Cypher === false)
					throw new Exception('bad compression');
				return base64_encode($Cypher);
			}
			else
			{
				return base64_encode(openssl_encrypt($PlainText, 'AES-256-CBC', $KEY, OPENSSL_RAW_DATA, $IV));
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	public function OldEncrypt($PlainText,$KEY=NULL,$IV=NULL,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		if(!isset($IV) or empty($IV))
			$IV = $this->IV;
		try{
			$KEY = base64_decode($KEY);
			$IV = base64_decode($IV);
			if($IsCompress)
			{
				$Cypher = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $KEY, $this->PKCS7_Padding($PlainText), MCRYPT_MODE_CBC, $IV);
				$Cypher = gzcompress($Cypher,9);
				if($Cypher === false)
					throw new Exception('bad compression');
				return base64_encode($Cypher);
			}
			else
			{
				return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $KEY, $this->PKCS7_Padding($PlainText), MCRYPT_MODE_CBC, $IV));
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	public function Encrypt($PlainText,$KEY,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		try{
			$KEY = base64_decode($KEY);
			if($IsCompress)
			{
				$Cypher = $this->SWAP(mcrypt_encrypt(MCRYPT_BLOWFISH, $KEY, $this->SWAP($this->PKCS5_Padding($PlainText)), 'ecb'));
				$Cypher = gzcompress($Cypher,9);
				if($Cypher === false)
					throw new Exception('bad compression');
				return base64_encode($Cypher);
			}
			else
			{
				return base64_encode($this->SWAP(mcrypt_encrypt(MCRYPT_BLOWFISH, $KEY, $this->SWAP($this->PKCS5_Padding($PlainText)), 'ecb')));
			}
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	public function Decrypt($Cypher,$KEY,$IsCompress=false)
	{
		if(!isset($KEY) or empty($KEY))
			$KEY = $this->KEY;
		try{
			$Cypher = base64_decode($Cypher);
			if($IsCompress)
			{
				$Cypher = gzuncompress($Cypher);
				if($Cypher === false)
					throw new Exception('bad compression');
			}
			$KEY = base64_decode($KEY);
			return $this->PKCS5_UnPadding($this->SWAP(mcrypt_decrypt(MCRYPT_BLOWFISH, $KEY, $this->SWAP($Cypher), 'ecb')));
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	private function PKCS7_UnPadding($value)
	{
		try{
			$blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
			$packing = ord($value[strlen($value) - 1]);
			if($packing && $packing < $blockSize)
			{
				for($P = strlen($value) - 1; $P >= strlen($value) - $packing; $P--)
				{
					if(ord($value{$P}) != $packing)
					{
						$packing = 0;
					}
				}
			}
			return substr($value, 0, strlen($value) - $packing);
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	private function PKCS7_Padding($value)
	{
		try{
			$block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
			$len = strlen($value);
			$padding = $block - ($len % $block);
			$value .= str_repeat(chr($padding),$padding);
			return $value;
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	private function PKCS5_Padding($data)
	{
		try{
			$padlen = 8-(strlen($data) % 8);
			for ($i=0; $i<$padlen; $i++)
			$data .= chr($padlen);
			return $data;
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	private function PKCS5_UnPadding($data)
	{
		try{
			$padlen = ord(substr($data, strlen($data)-1, 1));
			if ($padlen>8)
			return $data;
			for ($i=strlen($data)-$padlen; $i<strlen($data); $i++) {
				if (ord(substr($data, $i, 1)) != $padlen)
				return false;
			}
			return substr($data, 0, strlen($data)-$padlen);
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	private function SWAP($data)
	{
		try{
			$res="";
			for ($i=0; $i<strlen($data); $i+=4) {
				list(,$val) = unpack('N', substr($data, $i, 4));
				$res .= pack('V', $val);
			}
			return $res;
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function GenKeyPair($keysize=4096)
	{
		if(intval($keysize) < 1024 or intval($keysize) > 102400)
			$keysize = 4096;
		try{
			$res = openssl_pkey_new(array(
			"digest_alg" => "sha512",
			"private_key_bits" => $keysize,
			"private_key_type" => OPENSSL_KEYTYPE_RSA,
			));
			openssl_pkey_export($res, $privKey);
			$pubKey = openssl_pkey_get_details($res);
			$pubKey = $pubKey["key"];
			$this->PRVKEY = $privKey;
			$this->PUBKEY = $pubKey;
			return array('PUBKEY'=>$pubKey,'PRVKEY'=>$privKey);
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function RSAEncrypt($PlainText,$PUBKEY=null)
	{
		try{
			if(!isset($PUBKEY) or empty($PUBKEY))
				$PUBKEY = $this->PUBKEY;
			if(openssl_public_encrypt($PlainText, $encrypted, $PUBKEY))
				return base64_encode($encrypted);
			else
				null;
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function RSADecrypt($Cypher,$PRVKEY=null)
	{
		try{
			if(!isset($PRVKEY) or empty($PRVKEY))
				$PRVKEY = $this->PRVKEY;
			if(openssl_private_decrypt(base64_decode($Cypher), $decrypted, $PRVKEY))
				return $decrypted;
			else
				null;
		}
		catch(Exception $ex){
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function getLastError()
	{
		return $this->LastError;
	}
}
?>
