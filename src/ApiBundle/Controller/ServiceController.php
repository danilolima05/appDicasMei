<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\Service;
use ApiBundle\Form\ServiceType;
use Swagger\Annotations as SWG;
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
     *
     * @SWG\Get(
     *     path="api/services/",
     *     tags={"services"},
     *     operationId="getService",
     *     description="route to get all services ",
     *     @SWG\Response(
     *         response=200,
     *         description="Request executed with success"
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Internal error"
     *     )
     * )
     *
     */
    public function getAction()
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
     *
     * @SWG\Get(
     *     path="api/services/{id}",
     *     tags={"services"},
     *     operationId="getServiceById",
     *     description="route to get a specific service ",
     *     @SWG\Parameter(
     *         description="Service id to get",
     *         in="path",
     *         name="serviceId",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Request executed with success"
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Internal error"
     *     )
     * )
     *
     */
    public function getByIdAction($id)
    {
        $response = [
            "success" => false,
            "message" => null
        ];
        $status = 200;

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
            $status = 500;
        }

        return new JsonResponse($response, $status);
    }


    /**
     * @Route("/", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     *
     * @SWG\Post(
     *     path="api/services/",
     *     tags={"services"},
     *     operationId="createService",
     *     description="Post to create a Service, fields requireds: title, description, price (float), recurrence = {monthly, yearly}, timeToPay (time that you can split the price. eg: 12)",
     *     @SWG\Response(
     *         response=200,
     *         description="Request executed with success"
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Internal error"
     *     )
     * )
     */
    public function saveAction(Request $request)
    {

        $response = [
            'success' => false,
            'message' => null
        ];
        $status = 200;

        try {
            $data = $request->getContent();
            parse_str($data, $dataArr);

            $service = new Service();
            $form = $this->createForm(ServiceType::class, $service);
            $form->submit($dataArr);

            $manager = $this->getDoctrine()->getManager();

            $currentDate = new \DateTime('now', new \DateTimeZone("America/Sao_Paulo"));
            $service->setCreatedAt($currentDate);
            $service->setUpdatedAt($currentDate);

            $manager->persist($service);
            $manager->flush();

            $response = [
                'success' => true,
                'message' => 'Service created with success'
            ];
        }
        catch (\Exception $e) {
            $response['message'] = $e->getMessage();
            $status = 500;
        }

        return new JsonResponse($response, $status);
    }

    /**
     * @Route("/", methods={"PUT"})
     * @param Request $request
     * @return JsonResponse
     *
     * @SWG\Put(
     *     path="api/services/",
     *     tags={"services"},
     *     operationId="updateService",
     *     description="Post to create a Service, fields required: id, title, description, price (float), recurrence = {monthly, yearly}, timeToPay (time that you can split the price. eg: 12)",
     *     @SWG\Response(
     *         response=200,
     *         description="Request executed with success"
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Internal error"
     *     )
     * )
     */
    public function updateAction(Request $request)
    {
        $response = [
            'success' => false,
            'message' => null
        ];
        $status = 200;

        try {
            $data = $request->getContent();
            parse_str($data, $dataArr);

            $service = $this->getDoctrine()->getRepository("ApiBundle:Service")->find($dataArr['id']);

            if(empty($service)) {
                $response['message'] = "Service not found";
                return new JsonResponse($response);
            }

            $form = $this->createForm(ServiceType::class, $service);
            $form->submit($dataArr);

            $manager = $this->getDoctrine()->getManager();

            $currentDate = new \DateTime('now', new \DateTimeZone("America/Sao_Paulo"));
            $service->setUpdatedAt($currentDate);

            $manager->persist($service);
            $manager->flush();

            $response = [
                'success' => true,
                'message' => 'Service created with success'
            ];
        }
        catch (\Exception $e) {
            $response['message'] = $e->getMessage();
            $status = 500;
        }

        return new JsonResponse($response, $status);
    }

    /**
     *
     * @Route("/{id}", methods={"DELETE"})
     * @param $id
     * @return JsonResponse
     *
     * @SWG\Delete(
     *     path="api/services/{id}",
     *     tags={"services"},
     *     operationId="deleteService",
     *     description="route to delete an Service ",
     *     @SWG\Parameter(
     *         description="Service id to delete",
     *         in="path",
     *         name="serviceId",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Request executed with success"
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Internal error"
     *     )
     * )
     */
    public function deleteAction($id)
    {
        $response = [
            "success" => false,
            "message" => null
        ];
        $status = 200;

        try {
            $doctrine = $this->getDoctrine();
            $service = $doctrine->getRepository("ApiBundle:Service")->find($id);

            if ($service) {
                $manager = $doctrine->getManager();
                $manager->remove($service);
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
            $status = 500;
        }

        return new JsonResponse($response, $status);
    }
}
