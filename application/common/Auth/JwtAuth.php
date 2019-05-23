<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/5/22
 * Time: 3:56 PM
 */
namespace app\common\Auth;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;


/**
 * 单列 一次请求中所有出现使用jwt的地方都是一个用户
 *
 * class JwtAuth
 * @package App\common\Auth
 */
class JwtAuth
{
    /**
     * jwt token
     * @var
     */
    private $token;

    /**
     * jwt token
     * @var string
     */
    private $iss = 'api.test.com';

    /**
     * @var string
     */
    private $aud = 'imooc_server_app';

    /**
     * claim uid
     * @var
     */
    private $uid;


    /**
     *
     * @var string
     */
    private $secrect = '@#@#@@#@EWE@3232wewe@!@';


    /**
     * jwt decodeToken
     * @var
     */
    private $decodeToken;

    /**
     * 单列模式 jwtAuth句柄
     * @var
     */
    private static $instance;

    /**
     * 获取jwtAuth的句柄
     * @return JwtAuth
     */
    public  static function getInstance()
    {
        if(!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 私有构造函数
     * JwtAuth constructor.
     */
    private function __construct()
    {

    }

    /**
     * 私有化克隆函数
     */
    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * 获取Token
     * @return string
     */
    public function getToken()
    {
        return (string)$this->token;
    }

    /**
     * 设置token
     * @param $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * uid
     * @param $uid
     * @return $this
     */
    public function setUid($uid)
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * 编码jwt token
     * @return $this
     */
    public function encode()
    {
        $time = time();
        $this->token = (new Builder())->setHeader('alg', 'HS256')
            ->setIssuer($this->iss)
            ->setAudience($this->aud)
            ->setIssuedAt($time)
            ->setExpiration($time + 3600)
            ->set('uid', $this->uid)
            ->sign(new Sha256(), $this->secrect)
            ->getToken();

        return $this;
    }

    public function decode()
    {
        if(!$this->decodeToken){
            $this->decodeToken = (new Parser())->parse((string)$this->token);
            $this->uid = $this->decodeToken->getClaim();
        }
    }
}