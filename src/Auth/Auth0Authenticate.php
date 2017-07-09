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
        $subType = explode('|', $decodedToken->sub);
        switch ($subType[0]) {
            case 'auth0':
                $user = $this->_findUser($subType[1]); //get the id from the token.
                break;
            case 'facebook':
                // Set lookup field.
                $this->config(['fields' => ['username' => 'external_id']]);
                // This uses full $decodedToken->sub instead of $subType[1] because in the database the external_id is stored as facebook|1323453453564. This is so I can tell if it is Facebook login or Google.
                $user = $this->_findUser($decodedToken->sub);
                break;
            case 'google-oauth2':
                $this->config(['fields' => ['username' => 'external_id']]);
                $user = $this->_findUser($decodedToken->sub);
                break;
            default:
                return false; //type not supported.
                break;
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
