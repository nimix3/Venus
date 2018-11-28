<?php
// BasicAuth Class Library V.1 By devster and edited by NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// Micro PHP HTTP authentication library https://github.com/devster/uauth is under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\t3rdparty;
class BasicAuth
{
    protected $user;
    protected $password;
    protected $realm;
    protected $verify;
    protected $deny;

    public function __construct($realm = "Secured Area", array $allowedUsers = array())
    {
        $this->realm  = $realm;
        $this->verify = function ($user, $password) use ($allowedUsers) {
            return isset($allowedUsers[$user]) && $allowedUsers[$user] == $password;
        };
    }

    public function Realm($realm)
    {
        $this->realm = $realm;
        return $this;
    }

    public function Verify(callable $verify)
    {
        $this->verify = $verify;
        return $this;
	}
	
    public function Deny(callable $deny)
    {
        $this->deny = $deny;
        return $this;
    }

    public function Auth()
    {
        $user     = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
        $password = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null;
        if (is_null($user) || !(bool) call_user_func($this->verify, $user, $password)) {
            header(sprintf('WWW-Authenticate: Basic realm="%s"', $this->realm));
            header('HTTP/1.0 401 Unauthorized');
            if ($this->deny) {
                call_user_func($this->deny, $user);
            }
            exit;
        }
        $this->user     = $user;
        $this->password = $password;
        return $this;
    }

    public function GetUser()
    {
        return $this->user;
    }

    public function GetPassword()
    {
        return $this->password;
    }
}