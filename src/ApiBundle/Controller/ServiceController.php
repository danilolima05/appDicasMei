<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\Service;
use ApiBundle\Form\ServiceType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/services")
 */
class ServiceController extends Controller
{
    /**
     * @Route("/", methods={"GET"})
     * @return Response
     */
    public function indexAction()
    {
        $services = $this->getDoctrine()->getRepository("ApiBundle:Service")->findAll();

        $services = $this->get('jms_serializer')->serialize($services, 'json');
        return new Response($services);
    }

    /**
     *
     * @Route("/{id}", methods={"GET"})
     * @param $id
     * @return JsonResponse
     */
    public function getByIdAction($id)
    {
        $response = [
            "success" => false,
            "message" => null
        ];

        try {
            $doctrine = $this->getDoctrine();
            $service = $this->getDoctrine()->getRepository("ApiBundle:Service")->find($id);

            if ($service) {
                $service = $this->get('jms_serializer')->serialize($service, 'json');
                return new Response($service);
            }

            $response['message'] = "Service not found";
        }
        catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return new JsonResponse($response);
    }


    /**
     * @Route("/", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAction(Request $request)
    {

        $response = [
            'success' => false,
            'message' => null
        ];

        try {
            $data = $request->getContent();
            parse_str($data, $dataArr);

            $user = new Service();
            $form = $this->createForm(ServiceType::class, $user);
            $form->submit($dataArr);

            $manager = $this->getDoctrine()->getManager();

            $currentDate = new \DateTime('now', new \DateTimeZone("America/Sao_Paulo"));
            $user->setCreatedAt($currentDate);
            $user->setUpdatedAt($currentDate);

            $manager->persist($user);
            $manager->flush();

            $response = [
                'success' => true,
                'message' => 'Service created with success'
            ];
        }
        catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return new JsonResponse($response);
    }

    /**
     * @Route("/", methods={"PUT"})
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAction(Request $request)
    {
        $response = [
            'success' => false,
            'message' => null
        ];

        try {
            $data = $request->getContent();
            parse_str($data, $dataArr);

            $user = $this->getDoctrine()->getRepository("ApiBundle:Service")->find($dataArr['id']);

            if(empty($user)) {
                $response['message'] = "Service not found";
                return new JsonResponse($response);
            }

            $form = $this->createForm(ServiceType::class, $user);
            $form->submit($dataArr);

            $manager = $this->getDoctrine()->getManager();

            $currentDate = new \DateTime('now', new \DateTimeZone("America/Sao_Paulo"));
            $user->setUpdatedAt($currentDate);

            $manager->persist($user);
            $manager->flush();

            $response = [
                'success' => true,
                'message' => 'Service created with success'
            ];
        }
        catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return new JsonResponse($response);
    }

    /**
     *
     * @Route("/{id}", methods={"DELETE"})
     * @param $id
     * @return JsonResponse
     */
    public function deleteAction($id)
    {
        $response = [
            "success" => false,
            "message" => null
        ];

        try {
            $doctrine = $this->getDoctrine();
            $user = $doctrine->getRepository("ApiBundle:Service")->find($id);

            if ($user) {
                $manager = $doctrine->getManager();
                $manager->remove($user);
                $manager->flush();

                return new JsonResponse([
                    "success" => true,
                    "message" => "Service removed with success"
                ]);
            }

            $response["message"] = "Service not found";
        }
        catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return new JsonResponse($response);
    }
}
