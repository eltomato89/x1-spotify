<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserProfileController extends PolyfillController
{
    /**
     * @Route("/user/apikey", name="userprofile_apikey_reset", methods={"POST"})
     */
    public function userprofileApikeyReset(Request $request)
    {
        $this->getUserDomain()->setApiKey(md5(rand(1111111,999999)));
        $this->getDoctrine()->getManager()->flush();

        if($request->headers->has('referer')) {
            return new RedirectResponse($request->headers->get('referer'));
        }

        return new RedirectResponse("/");
    }
}
