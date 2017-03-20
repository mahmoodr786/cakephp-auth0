<?php
namespace Mahmoodr786\AuthZero\Auth;

use Cake\Auth\BaseAuthenticate;
use Cake\Controller\ComponentRegistry;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Utility\Security;
use Exception;

/**
 * @copyright 2017 MahmoodR
 * @license MIT
 *
 */
class Auth0Authenticate extends BaseAuthenticate
{
   
    public function __construct(ComponentRegistry $registry, $config)
    {
        $this->config([
            'header' => 'authorization',
            'prefix' => 'Bearer',
            'fields' => ['username' => 'id'],
            'userModel' => true,
            'client_id' => '',
            'secret' => '',
        ]);

        parent::__construct($registry, $config);
    }

    /**
     * authenticate method
     * @param  Request  $request  cake request
     * @param  Response $response cake response
     * @return BOOL or $user        
     */

    public function authenticate(Request $request, Response $response)
    {
        return $this->getUser($request);   
    }
     public function getUser(Request $request)
    {
        $decodedToken = null;
        $token = null;
        $config = $this->_config;
        $header = $request->header($config['header']);
        if($header){
            $token = str_replace($config['prefix'] . ' ', '', $header);
        }
        
        if(is_null($token)){
            return false;
        }

        if(empty($config['client_id'])){
            return false;
        }

        if(empty($config['secret'])){
            return false;
        }

        try {
            $decodedToken = \Auth0\SDK\Auth0JWT::decode($token, $config['client_id'], base64_encode($config['secret']));
        } catch(\Auth0\SDK\Exception\CoreException $e) {
            return false;
        }
        
        if (!$this->_config['userModel']) {
            return $decodedToken;
        }
        $id = str_replace('auth0|','',$decodedToken->sub); //remove auth0| from sub
        $user = $this->_findUser($id);

        if (!$user) {
            return false;
        }

        return $user;
    }
    /**
     * Unauthenticated method
     * @throws \Cake\Network\Exception\UnauthorizedException
     */
    public function unauthenticated(Request $request, Response $response)
    {
        throw new \Cake\Network\Exception\UnauthorizedException("401 Unauthorized");
    }
}
