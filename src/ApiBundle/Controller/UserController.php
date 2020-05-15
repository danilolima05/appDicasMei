<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class UserController extends Controller
{
    /**
     * @Route("/user")
     */
    public function getAction()
    {
        return new JsonResponse(['status' => true]);
    }
}
