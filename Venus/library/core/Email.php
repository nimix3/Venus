<?php
// Email Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class Email
{
	public $SMTPMailer;
	public $PHPMailer;

	public function __construct($new=false)
	{
		if($new)
		{
			$this->SMTPMailer = new SMTPMailer();
			$this->PHPMailer= new PHPMailer();
		}
	}
}

class SMTPMailer 
{
	protected $host;
	protected $port;
	protected $user;
	protected $pass;
	public $security = 'ssl';
	protected $subject;
	protected $message;
	public $type = 'text/html';
	public $encoding = 'UTF-8';
	public $error;
	public $debug = false;
	protected $from;
	protected $to = array();
	protected $LastError;
	
	public function setCredential($host,$port,$username,$password)
	{
		$this->host = $host;
		$this->port = $port;
		$this->user = $username;
		$this->pass = $password;
	}
	
	public function setSubject($subject) {
		$this->subject = $subject;
	}
	
	public function setMessage($message) {
		$this->message = $message;
	}

	public function setFrom($address, $name = null) {
		if (empty($name))
			$this->from = '<' . $address . '>';
		else
			$this->from = '"' . $name . '" <' . $address . '>';
	}

	public function setTo($address, $name = null) {
		if (empty($name))
			$this->to[] = '<' . $address . '>';
		else
			$this->to[] = '"' . $name . '" <' . $address . '>';
	}

	public function send() {
		$host = $this->host;
		if ($this->security == 'ssl')
			$host = 'ssl://' . $host;
		$socket = fsockopen($host, $this->port, $errno, $errstr);
		if ($socket === false) {
			$this->error = $errno . ' ' . $errstr;
			return false;
		} else if ($this->parse_result($socket, 220) === false)
			return false;
		$commands = array(
			'EHLO ' . $this->host => 250
		);
		if ($this->security == 'tls')
			$commands = array_merge($commands, array(
				'STARTTLS' => 220,
				'EHLO  ' . $this->host => 250
			));
		$commands = array_merge($commands, array(
			'AUTH LOGIN' => 334,
			base64_encode($this->user) => 334,
			base64_encode($this->pass) => 235,
			'MAIL FROM: ' . strstr($this->from, '<') => 250,
		));
		foreach ($this->to as $to)
			$commands['RCPT TO: ' . strstr($to, '<')] = 250;
		$commands = array_merge($commands, array(
			'DATA' => 354,
			'Subject: ' . $this->subject . "\r\n" .
				'To: ' . implode(', ', $this->to) . "\r\n" .
				'From: ' . $this->from . "\r\n" .
				'Content-Type: ' . $this->type . "\r\n" .
				'Content-Encoding: ' . $this->encoding . "\r\n\r\n" .
				$this->message => -1,
			'.' => 250,
			'QUIT' => 0
		));
		foreach ($commands as $command => $code) {
			fwrite($socket, $command . "\r\n");
			if ($code > -1 && $this->parse_result($socket, $code) === false) {
				$this->error .= ' (' . $command . ')';
				return false;
			}
			if ($command == 'STARTTLS' && stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) === false) {
				$this->error .= 'Unable to start TLS encryption. (' . $command . ')';
				return false;
			}
		}
		fclose($socket);
		return true;
	}

	private function parse_result($socket, $code) {
		$result = '';
		while (substr($result, 3, 1) != ' ')
			$result = fgets($socket, 256);
		if ($this->debug === true)
			echo $result . '<br>' . "\n";
		if (empty($code) || substr($result, 0, 3) == $code)
			return true;
		$this->error = $result;
		return false;
	}
	
	public function getLastError()
	{
		return $this->LastError;
	}
}


class PHPMailer
{
    protected $_wrap = 78;
    protected $_to = array();
    protected $_subject;
    protected $_message;
    protected $_headers = array();
    protected $_params;
    protected $_attachments = array();
    protected $_uid;
	protected $LastError;
	
    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->_to = array();
        $this->_headers = array();
        $this->_subject = null;
        $this->_message = null;
        $this->_wrap = 78;
        $this->_params = null;
        $this->_attachments = array();
        $this->_uid = $this->getUniqueId();
        return $this;
    }

    public function setTo($email, $name)
    {
        $this->_to[] = $this->formatHeader((string) $email, (string) $name);
        return $this;
    }

    public function getTo()
    {
        return $this->_to;
    }

    public function setFrom($email, $name)
    {
        $this->addMailHeader('From', (string) $email, (string) $name);
        return $this;
    }

    public function setCc(array $pairs)
    {
        return $this->addMailHeaders('Cc', $pairs);
    }

    public function setBcc(array $pairs)
    {
        return $this->addMailHeaders('Bcc', $pairs);
    }

    public function setReplyTo($email, $name = null)
    {
        return $this->addMailHeader('Reply-To', $email, $name);
    }

    public function setHtml()
    {
        return $this->addGenericHeader(
            'Content-Type', 'text/html; charset="utf-8"'
        );
    }

    public function setSubject($subject)
    {
        $this->_subject = $this->encodeUtf8(
            $this->filterOther((string) $subject)
        );
        return $this;
    }

    public function getSubject()
    {
        return $this->_subject;
    }

    public function setMessage($message)
    {
        $this->_message = str_replace("\n.", "\n..", (string) $message);
        return $this;
    }

    public function getMessage()
    {
        return $this->_message;
    }

    public function addAttachment($path, $filename = null, $data = null)
    {
		try{
			$filename = empty($filename) ? basename($path) : $filename;
			$filename = $this->encodeUtf8($this->filterOther((string) $filename));
			$data = empty($data) ? $this->getAttachmentData($path) : $data;
			$this->_attachments[] = array(
				'path' => $path,
				'file' => $filename,
				'data' => chunk_split(base64_encode($data))
			);
			return $this;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
    }

    public function getAttachmentData($path)
    {
        $filesize = filesize($path);
        $handle = fopen($path, "r");
        $attachment = fread($handle, $filesize);
        fclose($handle);
        return $attachment;
    }

    public function addMailHeader($header, $email, $name = null)
    {
        $address = $this->formatHeader((string) $email, (string) $name);
        $this->_headers[] = sprintf('%s: %s', (string) $header, $address);
        return $this;
    }

    public function addMailHeaders($header, array $pairs)
    {
        if (count($pairs) === 0) {
            $this->LastError[] = "You must pass at least one name => email pair.";
			return false;
        }
        $addresses = array();
        foreach ($pairs as $name => $email) {
            $name = is_numeric($name) ? null : $name;
            $addresses[] = $this->formatHeader($email, $name);
        }
        $this->addGenericHeader($header, implode(',', $addresses));
        return $this;
    }

    public function addGenericHeader($header, $value)
    {
        $this->_headers[] = sprintf(
            '%s: %s',
            (string) $header,
            (string) $value
        );
        return $this;
    }

    public function getHeaders()
    {
        return $this->_headers;
    }

    public function setParameters($additionalParameters)
    {
        $this->_params = (string) $additionalParameters;
        return $this;
    }

    public function getParameters()
    {
        return $this->_params;
    }

    public function setWrap($wrap = 78)
    {
        $wrap = (int) $wrap;
        if ($wrap < 1) {
            $wrap = 78;
        }
        $this->_wrap = $wrap;
        return $this;
    }

    public function getWrap()
    {
        return $this->_wrap;
    }

    public function hasAttachments()
    {
        return !empty($this->_attachments);
    }

    public function assembleAttachmentHeaders()
    {
        $head = array();
        $head[] = "MIME-Version: 1.0";
        $head[] = "Content-Type: multipart/mixed; boundary=\"{$this->_uid}\"";
        return join(PHP_EOL, $head);
    }

    public function assembleAttachmentBody()
    {
        $body = array();
        $body[] = "This is a multi-part message in MIME format.";
        $body[] = "--{$this->_uid}";
        $body[] = "Content-Type: text/html; charset=\"utf-8\"";
        $body[] = "Content-Transfer-Encoding: quoted-printable";
        $body[] = "";
        $body[] = quoted_printable_encode($this->_message);
        $body[] = "";
        $body[] = "--{$this->_uid}";
        foreach ($this->_attachments as $attachment) {
            $body[] = $this->getAttachmentMimeTemplate($attachment);
        }
        return implode(PHP_EOL, $body) . '--';
    }

    public function getAttachmentMimeTemplate($attachment)
    {
        $file = $attachment['file'];
        $data = $attachment['data'];
        $head = array();
        $head[] = "Content-Type: application/octet-stream; name=\"{$file}\"";
        $head[] = "Content-Transfer-Encoding: base64";
        $head[] = "Content-Disposition: attachment; filename=\"{$file}\"";
        $head[] = "";
        $head[] = $data;
        $head[] = "";
        $head[] = "--{$this->_uid}";
        return implode(PHP_EOL, $head);
    }

    public function send()
    {
		try{
			$to = $this->getToForSend();
			$headers = $this->getHeadersForSend();
			if (empty($to)) {
				$this->LastError[] = "Unable to send, no To address has been set.";
				return false;
			}
			if ($this->hasAttachments()) {
				$message  = $this->assembleAttachmentBody();
				$headers .= PHP_EOL . $this->assembleAttachmentHeaders();
			} else {
				$message = $this->getWrapMessage();
			}
			return mail($to, $this->_subject, $message, $headers, $this->_params);
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
    }

    public function debug()
    {
        return '<pre>' . print_r($this, true) . '</pre>';
    }

    public function __toString()
    {
        return print_r($this, true);
    }

    public function formatHeader($email, $name = null)
    {
        $email = $this->filterEmail((string) $email);
        if (empty($name)) {
            return $email;
        }
        $name = $this->encodeUtf8($this->filterName((string) $name));
        return sprintf('"%s" <%s>', $name, $email);
    }

    public function encodeUtf8($value)
    {
        $value = trim($value);
        if (preg_match('/(\s)/', $value)) {
            return $this->encodeUtf8Words($value);
        }
        return $this->encodeUtf8Word($value);
    }

    public function encodeUtf8Word($value)
    {
        return sprintf('=?UTF-8?B?%s?=', base64_encode($value));
    }

    public function encodeUtf8Words($value)
    {
        $words = explode(' ', $value);
        $encoded = array();
        foreach ($words as $word) {
            $encoded[] = $this->encodeUtf8Word($word);
        }
        return join($this->encodeUtf8Word(' '), $encoded);
    }

    public function filterEmail($email)
    {
        $rule = array(
            "\r" => '',
            "\n" => '',
            "\t" => '',
            '"'  => '',
            ','  => '',
            '<'  => '',
            '>'  => ''
        );
        $email = strtr($email, $rule);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return $email;
    }

    public function filterName($name)
    {
        $rule = array(
            "\r" => '',
            "\n" => '',
            "\t" => '',
            '"'  => "'",
            '<'  => '[',
            '>'  => ']',
        );
        $filtered = filter_var(
            $name,
            FILTER_SANITIZE_STRING,
            FILTER_FLAG_NO_ENCODE_QUOTES
        );
        return trim(strtr($filtered, $rule));
    }

    public function filterOther($data)
    {
        return filter_var($data, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
    }

    public function getHeadersForSend()
    {
        if (empty($this->_headers)) {
            return '';
        }
        return join(PHP_EOL, $this->_headers);
    }

    public function getToForSend()
    {
        if (empty($this->_to)) {
            return '';
        }
        return join(', ', $this->_to);
    }

    public function getUniqueId()
    {
        return md5(uniqid(time()));
    }

    public function getWrapMessage()
    {
        return wordwrap($this->_message, $this->_wrap);
    }
	
	public function getLastError()
	{
		return $this->LastError;
	}
}
?>