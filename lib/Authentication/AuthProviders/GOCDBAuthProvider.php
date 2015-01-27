<?php
namespace org\gocdb\security\authentication;

require_once __DIR__.'/../IAuthenticationProvider.php'; 
require_once __DIR__.'/../IAuthentication.php'; 
require_once __DIR__.'/../Exceptions/AuthenticationException.php'; 
require_once __DIR__.'/../Exceptions/BadCredentialsException.php'; 
require_once __DIR__.'/../Exceptions/UsernameNotFoundException.php'; 
require_once __DIR__.'/../IUserDetails.php'; 
require_once __DIR__.'/../ApplicationSecurityConfigService.php'; 
require_once __DIR__.'/../AuthTokens/X509AuthenticationToken.php'; 
require_once __DIR__.'/../AuthTokens/SimpleSamlPhpAuthToken.php'; 
require_once __DIR__.'/../AuthTokens/UsernamePasswordAuthenticationToken.php'; 


/**
 * The GOCDB Authentication provider.  
 * Supports X509AuthenticationToken and SimpleSamlPhpAuthToken tokens. 
 *
 * @author David Meredith 
 */
class GOCDBAuthProvider implements IAuthenticationProvider {
    
    public function authenticate(IAuthentication $auth){
        if($auth == null){
            throw new BadCredentialsException(null, 'Bad credentials - null given'); 
        }
        if(!$this->supports($auth)){
            //throw new BadCredentialsException(null, 'Bad credentials - unsupported token');
            // the implementation may return null if it is unable to support 
            // authentication of the passed Authentication object
            return null; 
        }
        // If a un/pw token is given, then we have to use the username to  
        // load a user from the DB and then compare the hashed password against
        // the hashed pw stored in the DB. Only then can we authenticate.   
        //if($auth instanceof UsernamePasswordAuthenticationToken){
        //    return $this->authenticateAgainstDB($auth); 
        //}

        // If not un/pw token, the x509 and SimpleSaml_with_x509SamlAttribute 
        // prove the user has a certificate, so we can authenticate our user.  
        $x509DN = $auth->getPrinciple() ;
        if($x509DN == null || $x509DN == ''){
            throw new AuthenticationException(null, 'x509 DN could not be extracted from token ['.  get_class($auth).']');
        }
        $roles = array();  
        $roles[] = 'ROLE_CERTOWNER';
        $auth->setAuthorities($roles); 
        return $auth; 
    }

    /**
     * TODO - If a un/pw token is given, then we have to use the username to  
     * load a user from the DB and then compare the hashed password against
     * the hashed pw stored in the DB. Only then can we authenticate.   
     */ 
    private function authenticateAgainstDB($auth){    
        // Perform Authentication: 
        // You may need to customize this logic. In Spring this is absracted 
        // using different ProviderManager implementations that must support:   
        // 'authenticate(IAuthentication)throws AuthenticationException' 
        try { 
            $username = $auth->getPrinciple(); 
            // Spring way...(if $auth.principle was previously updated to be a IUserDetails) 
            //if($username instanceof IUserDetails) $username = $username->getUsername(); 
           
            // Now attempt to load the user's details from the DB
            $userDetails = ApplicationSecurityConfigService::getUserDetailsService()->loadUserByUsername($username);  
        } catch(UsernameNotFoundException $ex){
            // if auth->getPrinciple is not null, then this could be a 
            // new user without a recognised DN ! so throwing an AuthException 
            // would be wrong - TODO! 
            throw new AuthenticationException($ex, 'Username not found');  
        }
        // Auth is usually done by comparing principle and password value equality  
        // between the returned $userDetails object and the given $auth token.  
        // Note, getPassword() never returns null, even for auth mechanisms that 
        // don't use a password in which case an empty string is returned. This 
        // allows the same auth logic across different mechanisms (e.g. x509).  
        if($userDetails->getUsername() == $auth->getPrinciple() && 
                $userDetails->getPassword() == $auth->getCredentials()){
          
           // Spring way...(most spring auth providers update (re-set) the $auth->principle 
           // to be a IUserDetails implementation, e.g.  
           //$auth->setPrinciple($userDetails);  
            
           //$auth->setDetails($userDetails);
           // set UserDetails as Doctrine 'User' entity or null
           $auth->setDetails($userDetails->getGOCDBCustomVal());
           $auth->setAuthorities($userDetails->getAuthorities()); 
           return $auth; 
        }
        // We didn't manage to authenticate the user, so throw exception 
        throw new AuthenticationException(null, 'Authentication failed');  
    }


    public function supports(IAuthentication $auth){
        if($auth instanceof X509AuthenticationToken){
            return true; 
        }
        if($auth instanceof SimpleSamlPhpAuthToken){
            return true; 
        }
        return false; 
    }

    
}

?>
