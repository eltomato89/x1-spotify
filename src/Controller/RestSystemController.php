<?php
/**
 * Created by PhpStorm.
 * User: koeh112
 * Date: 08.10.17
 * Time: 17:02
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RestSystemController extends AbstractController
{

    /**
     * @Route("/REST/v1/system/servertime", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function servertimeAction(Request $request)
    {
        $result = array("servertime" => time());
        
        if ($content = $request->getContent()) {
            $parameters = json_decode($content, true);
            if(isset($parameters["localtime"])) $result["submitted"] = $parameters["localtime"];
        }

        return new JsonResponse($result, 200);
        
    }

    /**
     * @Route("/{anything}", name="wildcard", defaults={"anything" = null}, requirements={"anything"=".+"}, methods={"OPTIONS"})
     * @return JsonResponse
     */
    public function corsAction()
    {
        return new JsonResponse(null, 200, ["X-CORS-TEST" => "yes"]);
    }

}