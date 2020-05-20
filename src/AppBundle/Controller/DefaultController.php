<?php

namespace AppBundle\Controller;

use ApiBundle\Services\PagarMeService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request, PagarMeService $pagarMeService)
    {

//        $return = $pagarMeService->getAddressByZipCode('17208270');

//        $user = $this->getDoctrine()->getRepository("ApiBundle:User")->find(2);
//        $return = $pagarMeService->createCustomer($user);

        return $this->redirectToRoute('app.swagger_ui');
    }
}
