<?php
define('__ROOT__', dirname(dirname(dirname(dirname(__FILE__)))));
require_once __ROOT__.'/vendor/openid/php-openid/Auth/OpenID/SReg.php';

/**
 * Copyright 2014 Openstack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
class OpenStackIdAuthenticator extends Controller
{

    /**
     * @var IMemberRepository
     */
    private $member_repository;

    public function __construct()
    {
        parent::__construct();
        $this->member_repository = new SapphireCLAMemberRepository();
    }

    function index()
    {

        try {

            $consumer = Injector::inst()->get('MyOpenIDConsumer');

            // Complete the authentication process using the server's response.
            $response = $consumer->complete(OpenStackIdCommon::getReturnTo());

            if ($response->status == Auth_OpenID_CANCEL) {

                throw new Exception('The verification was cancelled. Please try again.');

            } else if ($response->status == Auth_OpenID_FAILURE) {

                throw new Exception("The OpenID authentication failed.");

            } else if ($response->status == Auth_OpenID_SUCCESS) {

                $openid = $response->getDisplayIdentifier();
                $openid = OpenStackIdCommon::escape($openid);

                if ($response->endpoint->canonicalID) {
                    $openid = escape($response->endpoint->canonicalID);
                }
                //get user info from openid response
                list($email, $full_name) = $this->getUserProfileInfo($response);
                //try to get user by email
                $member = $this->member_repository->findByEmail($email);
                if(!$member){// or by openid
                    $member = Member::get()->filter('IdentityURL', $openid)->first();
                }
                if ($member) {
                    $member->setIdentityUrl($openid);
                    $member->write();
                    $member->LogIn(true);
                    if ($backURL = Session::get("BackURL")) {
                        Session::clear("BackURL");
                        return $this->redirect($backURL);
                    } else {
                        return $this->redirectBack();
                    }
                }
                throw new Exception("The OpenID authentication failed.");
            }
        } catch (Exception $ex) {
            Session::set("Security.Message.message", $ex->getMessage());
            Session::set("Security.Message.type", "bad");
            return $this->redirect("Security/badlogin");
        }
    }

    private function getUserProfileInfo($response)
    {
        if (Auth_OpenID_supportsSReg($response->endpoint)) {
            $sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
            $sreg      = $sreg_resp->contents();
            $email     = @$sreg['email'];
            $full_name = @$sreg['fullname'];
        } else {
            //AX
            // Get registration informations
            $ax = new Auth_OpenID_AX_FetchResponse();
            $obj = $ax->fromSuccessResponse($response);
            $email = $obj->data["http://axschema.org/contact/email"][0];
            if (isset($obj->data["http://axschema.org/namePerson/first"]))
                $full_name = $obj->data["http://axschema.org/namePerson/first"][0] . ' ' . $obj->data["http://axschema.org/namePerson/last"][0];
            else
                $full_name = $obj->data["http://axschema.org/namePerson"][0];
        }
        return array($email, $full_name);
    }
}