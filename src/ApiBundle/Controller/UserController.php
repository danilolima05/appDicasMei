<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\User;
use ApiBundle\Form\UserType;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/users")
 */
class UserController extends Controller
{
    /**
     * @Route("/", methods={"GET"})
     * @return JsonResponse
     *
     * @SWG\Get(
     *     path="api/users/",
     *     tags={"users"},
     *     operationId="getUser",
     *     description="route to get all users",
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
    public function indexAction()
    {
        $users = $this->getDoctrine()->getRepository("ApiBundle:User")->findAll();

        $users = $this->get('jms_serializer')->serialize($users, 'json');
        return new Response($users);
    }

    /**
     *
     * @Route("/{id}", methods={"GET"})
     * @param $id
     * @return JsonResponse
     *
     * @SWG\Get(
     *     path="api/users/{id}",
     *     tags={"users"},
     *     operationId="getUserById",
     *     description="route to get a specific user ",
     *     @SWG\Parameter(
     *         description="User id to get",
     *         in="path",
     *         name="userId",
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
    public function getByIdAction($id)
    {
        $response = [
            "success" => false,
            "message" => null
        ];
        $status = 200;

        try {
            $doctrine = $this->getDoctrine();
            $user = $this->getDoctrine()->getRepository("ApiBundle:User")->find($id);

            if ($user) {
                $user = $this->get('jms_serializer')->serialize($user, 'json');
                return new Response($user);
            }

            $response['message'] = "User not found";
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
     *     path="api/users/",
     *     tags={"users"},
     *     operationId="createUser",
     *     description="Post to create an User, fields requireds: name, email, cpf, phone (+5514988888888), cep, address, neighborhood, city, state(eg: SP), country(eg: BR)",
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

            $user = new User();
            $form = $this->createForm(UserType::class, $user);
            $form->submit($dataArr);

            $this->getDoctrine()->getRepository("ApiBundle:User")->save($user);

            $response = [
                'success' => true,
                'message' => 'User created with success'
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
     * @SWG\Put(
     *     path="api/users/",
     *     tags={"users"},
     *     operationId="updateUser",
     *     description="route to update an User, fields requireds:id and the field you want to change",
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
        $statusCode = 200;

        try {
            $data = $request->getContent();
            parse_str($data, $dataArr);

            $user = $this->getDoctrine()->getRepository("ApiBundle:User")->find($dataArr['id']);

            if(empty($user)) {
                $response['message'] = "User not found";
                return new JsonResponse($response);
            }

            $form = $this->createForm(UserType::class, $user);
            $form->submit($dataArr);

            $this->getDoctrine()->getRepository("ApiBundle:User")->save($user);

            $response = [
                'success' => true,
                'message' => 'User updated with success'
            ];
        }
        catch (\Exception $e) {
            $response['message'] = $e->getMessage();
            $statusCode = 500;
        }

        return new JsonResponse($response, $statusCode);

    }

    /**
     *
     * @Route("/{id}", methods={"DELETE"})
     * @param $id
     * @return JsonResponse
     *
     * @SWG\Delete(
     *     path="api/users/{id}",
     *     tags={"users"},
     *     operationId="deleteUser",
     *     description="route to delete an User ",
     *     @SWG\Parameter(
     *         description="User id to delete",
     *         in="path",
     *         name="userId",
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
    public function deleteAction($id)
    {
        $response = [
            "success" => false,
            "message" => null
        ];
        $statusCode = 200;


        try {
            $doctrine = $this->getDoctrine();
            $user = $doctrine->getRepository("ApiBundle:User")->find($id);

            if ($user) {
                $manager = $doctrine->getManager();
                $manager->remove($user);
                $manager->flush();

                return new JsonResponse([
                    "success" => true,
                    "message" => "User removed with success"
                ]);
            }

            $response["message"] = "User not found";
        }
        catch (\Exception $e) {
            $response['message'] = $e->getMessage();
            $statusCode = 500;
        }

        return new JsonResponse($response, $statusCode);
    }
}
