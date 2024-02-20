<?php

namespace App\Infrastructure\Services;

use App\Infrastructure\Rest\JwtToken;
use App\Infrastructure\Rest\Response;
use App\Infrastructure\Utils\JsonUtils;
use App\Infrastructure\Utils\Properties;
use Firebase\JWT\JWT;

/*
 * Inspired by http://fr.wikihow.com/cr%C3%A9er-un-script-de-connexion-s%C3%A9curis%C3%A9e-avec-PHP-et-MySQL
 * This fork uses JSON as storage data
 */

/*
 * User management Utility.
 * Each user is stored in a separate JSON file.
 *
 * users/
 * -----/user1.json
 * -----/user2.json
 */
class AuthService
{
    /**
     * database directory.
     */
    private $databasedir;

    private $service;

    private $throttle;

    /*
     * Available hash : hash_algos()
     */
    private $algorithm = 'sha512';

    private $phpJwtAlgorithm = 'HS512';

    private $jwtImpl;

    /**
     * default salt length.
     */
    private $saltlength = 128;

    /**
     * depends on server hardware.
     */
    private $passwordHashCost = 12;

    /**
     * Constructor.
     *
     * @param string $databasedir eg : public
     */
    public function __construct(string $databasedir)
    {
        $this->databasedir = $databasedir;
        $this->service = new UserService($databasedir);
        $this->throttle = new ThrottleService($databasedir);
        $this->jwtImpl = Properties::getInstance()->getConf()->{'jwt'};
    }

    public function controlCaptcha($email, $captchaAnswer)
    {
        $captchaValidated = false;
        if ($this->throttle->isCaptchaCreated($email)) {
            $captchaObj = $this->throttle->getCaptcha($email);

            if ($captchaObj === null) {
                throw new \Exception('captchaObj is null');
            }

            if (isset($captchaObj) && isset($captchaObj->{'answer'}) && $captchaObj->{'answer'} === $captchaAnswer) {
                $captchaValidated = true;
            }
        } else {
            // no captcha required
            $captchaValidated = true;
        }

        return $captchaValidated;
    }

    /**
     * Authenticate
     * return an empty string if success.
     *
     * @param string $emailParam : email
     * @param string $password   : pseudo clear password (must be hashed from client)
     *
     * @return string return an empty string if success.
     */
    public function login($emailParam, $password, $captchaAnswer)
    {
        $loginmsg = 'default error message';

        // if someone forgot to do this before
        $email = strtolower($emailParam);

        // return the existing user
        $user = $this->service->getJsonUser($email);

        // user verification
        if (!empty($user)) {
            // captcha start verification
            $captchaValidated = $this->controlCaptcha($email, $captchaAnswer);
            if (!$captchaValidated) {
                $loginmsg = 'wrong captcha';
            }
            // captcha end

            if ($captchaValidated) {
                if ($captchaValidated && password_verify($password, $user->{'password'})) {
                    $loginmsg = '';

                    $this->throttle->archiveOldFailed($user->{'email'});
                } else {
                    $loginmsg = 'Wrong password';
                }
            } else {
                $failed = $this->throttle->saveFailedLogin($user->{'email'});
            }
        } else {
            $loginmsg = 'Wrong user';
        }

        return $loginmsg;
    }

    /**
     * Authenticate and return a User object with a token.
     *
     * @param string $emailParam : email
     * @param string $password   : pseudo clear password (must be hashed from client)
     *
     * @return Response object
     */
    public function getToken($emailParam, $password, $captchaAnswer = null): Response
    {
        if ('captchatest@example.com' === $emailParam) {
//            $foo = "bar";
        }

        // initialize Response
        $response = new Response();
        $response->setCode(401);
        $response->setResult(new \stdClass());

        $loginmsg = 'Wrong login';

        // if someone forgot to do this before
        $email = strtolower($emailParam);

        // return the existing user
        $user = $this->service->getJsonUser($email);
        // user verification
        if (!empty($user)) {
            // captcha start verification
            $captchaValidated = $this->controlCaptcha($email, $captchaAnswer);
            if (!$captchaValidated) {
                $loginmsg = 'wrong captcha';
            }
            // captcha end
            // password
            if ($captchaValidated && password_verify($password, $user->{'password'})) {
                $this->throttle->archiveOldFailed($user->{'email'});

                $token = null;
                $jwt = new JwtToken();
                if ('php-jwt' === $this->jwtImpl) {
                    $payload = $jwt->initPayload($user->{'name'}, $user->{'email'}, $user->{'role'});

                    $token = JWT::encode($payload, $user->{'salt'}, $this->phpJwtAlgorithm);
                } else {
                    // old jwt impl

                    $token = $jwt->createTokenFromUser(
                        $user->{'name'},
                        $user->{'email'},
                        $user->{'role'},
                        $user->{'salt'}
                    );
                    unset($jwt);
                }

                $response->setCode(200);
                $userResponse = json_decode('{}');
                $userResponse->{'name'} = $user->{'name'};
                $userResponse->{'username'} = $user->{'name'};
                $userResponse->{'email'} = $user->{'email'};
                $userResponse->{'role'} = $user->{'role'};
                $userResponse->{'clientalgorithm'} = $user->{'clientalgorithm'};
                $userResponse->{'newpasswordrequired'} = $user->{'newpasswordrequired'};
                /*
                if ($user->{'newpasswordrequired'} !== 'true') {
                $userResponse->{'token'} = $token;
                }
                 */
                $userResponse->{'token'} = $token;

                $response->setResult($userResponse);
                // success
                $loginmsg = '';
            } else {
                // incorrect password
                $loginmsg = 'wrong password';
                $failed = $this->throttle->saveFailedLogin($user->{'email'});

                $userResponse = json_decode('{}');
                $userResponse->{'name'} = $user->{'name'};
                $userResponse->{'username'} = $user->{'name'};
                $userResponse->{'email'} = $user->{'email'};
                $newCaptcha = $this->throttle->createCaptcha($user->{'email'});
                if ($newCaptcha != null) {
                    $userResponse->{'captcha'} = $newCaptcha->{'question'};
                }

                $response->setResult($userResponse);
            }
        } else {
            $loginmsg = 'wrong user';
        }
        // user found

        return $response;
    }

    /**
     * Authenticate and return a User object with a token.
     *
     * @param string $emailParam  : email
     * @param string $password    : pseudo clear password (must be hashed from client)
     * @param string $newPassword : pseudo clear password (must be hashed from client)
     *
     * @return Response object
     */
    public function changePassword($emailParam, $password, $newPassword, $captchaanswer): Response
    {
        // initialize Response
        $response = new Response();
        $response->setCode(401);

        $response->setResult(new \stdClass());

        $loginmsg = 'Wrong login';

        // if someone forgot to do this before
        $email = strtolower($emailParam);

        // return the existing user
        $user = $this->service->getJsonUser($email);

        // check login sent by mail
        if ($this->login($email, $password, $captchaanswer) === '') {
            //update with new password
            $updateMsg = $this->createUser('', $email, $newPassword, 'update');

            // return the existing user
            $user = $this->service->getJsonUser($email);
            $user->{'clientalgorithm'} = 'hashmacbase64';
            $user->{'newpasswordrequired'} = 'false';
            JsonUtils::writeJsonFile($this->service->getJsonUserFile($email), $user);

            if (empty($updateMsg)) {
                $response = $this->getPublicInfo($email);
            } else {
                $response->setError(500, 'createUserWithSecret error '.$updateMsg);
            }
        } else {
            // incorrect password
            $loginmsg = 'wrong password';
        }

        return $response;
    }

    public function getJsonUserFromToken($token): \stdClass
    {
        $jwt = new JwtToken();

        return $this->service->getJsonUser($jwt->getUserFromToken($token));
    }

    /**
     * Verify token.
     *
     * @param string $token        : token
     * @param string $requiredRole : role (editor ...)
     *
     * @return Response object
     */
    public function verifyToken($token, $requiredRole): Response
    {
        $response = $this->getDefaultResponse();

        if (strlen($token) === 0) {
            throw new \Exception('empty token');
        }

        $jwt = new JwtToken();

        // get payload and convert to JSON

        $payload = $jwt->getPayload($token);

        if (strlen($payload) === 0) {
            // @codeCoverageIgnoreStart
            throw new \Exception('empty payload');
            // @codeCoverageIgnoreEnd
        }

        $payloadJson = json_decode($payload);
        if (!isset($payloadJson)) {
            // @codeCoverageIgnoreStart
            throw new \Exception('empty payload');
            // @codeCoverageIgnoreEnd
        }
        // get the existing user

        $user = $this->service->getJsonUser($payloadJson->{'sub'});

        // verify token with secret
        if ($jwt->verifyToken($token, $user->{'salt'})) {
            if ($this->isPermitted($user, $requiredRole)) {
                $response->setCode(200);
            } else {
                // TODO : return 401 instead of 403 ?
                $response->setError(403, 'wrong role');
            }
        } else {
            //error_log('Http401_AuthService_verifyToken');
            $response->setError(401, 'verifyToken false');
        }

        return $response;
    }

    /**
     * Authenticate and return a User object with a token.
     *
     * @param string $emailParam  email
     * @param string $newPassword new password
     *
     * @return Response object
     */
    public function resetPassword($emailParam, $newPassword): Response
    {
        // initialize Response
        $response = new Response();
        $response->setCode(401);

        $response->setResult(new \stdClass());

        $loginmsg = 'Wrong login';

        // if someone forgot to do this before
        $email = strtolower($emailParam);

        // return the existing user
        $user = $this->service->getJsonUser($email);

        // user found
        if (!empty($user)) {
            $updateMsg = $this->createUser($emailParam, $emailParam, $newPassword, 'update');

            // return the existing user
            $user = $this->service->getJsonUser($email);
            $user->{'clientalgorithm'} = 'none';
            $user->{'newpasswordrequired'} = 'true';

            JsonUtils::writeJsonFile($this->service->getJsonUserFile($email), $user);

            $this->throttle->archiveOldFailed($emailParam);

            if (empty($updateMsg)) {
                $response = $this->getPublicInfo($email);
            } else {
                // @codeCoverageIgnoreStart
                // ignore test coverage : it should not happen and it is too specific
                $response->setError(500, 'resetPassword error '.$updateMsg);
                // @codeCoverageIgnoreEnd
            }
        }

        return $response;
    }

    /**
     * Public info of a user. Beware when doing updates.
     *
     * @param string $email user
     *
     * @return Response public info
     */
    public function getPublicInfo($email): Response
    {
        $response = $this->getDefaultResponse();
        if (\file_exists($this->service->getJsonUserFile($email))) {
            $user = $this->service->getJsonUser($email);
            if (isset($user)) {
                // do not send private info such as password ...
                $info = json_decode('{"name":"", "clientalgorithm":"", "newpasswordrequired":""}');

                JsonUtils::copy($user, $info);
                $captchaObject = $this->createCaptchaIfRequired($email);
                if ($captchaObject != null) {
                    $info->{'captcha'} = $captchaObject->{'question'};
                }

                $response->setResult($info);
                $response->setCode(200);
            }
        }

        return $response;
    }

    public function createCaptchaIfRequired($email)
    {
        $captchaResult = null;
        if ($this->throttle->isCaptchaRequired($email)) {
            $captchaResult = $this->throttle->createCaptcha($email);
        }

        return $captchaResult;
    }

    /**
     * Generate a US-ASCII random string.
     *
     * @param int $length to generate
     *
     * @return string US-ASCII random string
     */
    public function generateRandomString(int $length = 10): string
    {
        return base64_encode(random_bytes($length));
    }

    /**
     * Initialize a default Response object.
     *
     * @return Response object
     */
    protected function getDefaultResponse(): Response
    {
        $response = new Response();
        $response->setCode(400);
        $response->setResult(new \stdClass());

        return $response;
    }

    /**
     * Control if the current user has access to API.
     *
     * @param \stdClass $user         object
     * @param string    $requiredRole required role
     *
     * @return bool true if access is authorized
     */
    private function isPermitted(\stdClass $user, string $requiredRole): bool
    {
        $result = false;
        if (!empty($user) && !empty($user->{'role'}) && !empty($requiredRole)) {
            if ($requiredRole === 'editor') {
                $result = $this->isPermittedEditor($user);
            }

            if ($requiredRole === 'admin') {
                $result = $this->isPermittedAdmin($user);
            }
        }

        return $result;
    }

    /**
     * Control if the current user has access to an editor API.
     *
     * @param \stdClass $user object
     *
     * @return bool true if access is authorized
     */
    private function isPermittedEditor(\stdClass $user): bool
    {
        $result = false;
        if (!empty($user) && !empty($user->{'role'})) {
            if ($user->{'role'} === 'editor') {
                $result = true;
            } elseif ($user->{'role'} === 'admin') {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Control if the current user has access to an admin API.
     *
     * @param \stdClass $user object
     *
     * @return bool true if access is authorized
     */
    private function isPermittedAdmin($user): bool
    {
        $result = false;
        if (!empty($user) && !empty($user->{'role'})) {
            if ($user->{'role'} === 'admin') {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Create a new user.
     *
     * @param string $username   : username
     * @param string $emailParam : email
     * @param string $password   : password
     * @param string $mode       : values 'create' or 'update'
     *
     * @return string empty string if success
     */
    public function createUser(
        string $username,
        string $emailParam,
        string $password,
        string $mode
    ) {
        $email = strtolower($emailParam);

        $error_msg = null;

        if (empty($email)) {
            $error_msg .= 'EmptyEmail ';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // invalid email
            $error_msg .= 'InvalidEmail ';
        }

        if (empty($password)) {
            $error_msg .= 'EmptyPassword ';
        }

        if (empty($error_msg)) {
            $file = $this->service->getJsonUserFile($email);

            if ($mode === 'create') {
                if (empty($username)) {
                    $error_msg .= 'InvalidUser ';
                }

                if (file_exists($file)) {
                    // user already exists
                    $error_msg .= 'AlreadyExists.';
                }
            }
        }

        if (empty($error_msg)) {
            // create a random for this user
            // http://php.net/manual/fr/function.random-bytes.php
            $random_salt = base64_encode(random_bytes($this->saltlength));

            // http://stackoverflow.com/questions/8952807/openssl-digest-vs-hash-vs-hash-hmac-difference-between-salt-hmac
            // store a salted password

            $options = [
                'cost' => $this->passwordHashCost,
            ];
            $saltpassword = password_hash($password, PASSWORD_BCRYPT, $options);

            if ($mode === 'create') {
                // create user
                $this->service->addDbUser($email, $username, $saltpassword, $random_salt, 'guest');
            } else {
                //role is not modified
                $this->service->updateUser($email, '', $saltpassword, $random_salt, '');
            }
        }

        return $error_msg;
    }

    public function setJwtImpl(string $jwt)
    {
        $this->jwtImpl = $jwt;
    }
}
