<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\Transaction;
use ApiBundle\Entity\Service;
use ApiBundle\Entity\User;
use Swagger\Annotations as SWG;
use ApiBundle\Form\TransactionType;
use ApiBundle\Services\PagarMeService;
use http\Exception\RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/transactions")
 */
class TransactionController extends Controller
{
    /**
     * @Route("/", methods={"POST"})
     * @param Request $request
     * @param PagarMeService $pagarMeService
     * @return JsonResponse
     *
     * @SWG\Post(
     *     path="api/transactions/",
     *     tags={"transactions"},
     *     operationId="createTransaction",
     *     description="Post to create a Transaction, fields requireds: amount, user id, service id, cc_number, cc_cvv, cc_expiration_date (format: MMAA. eg: 1023), cc_name",
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
    public function saveAction(Request $request, PagarMeService $pagarMeService)
    {

        $response = [
            'success' => false,
            'message' => null
        ];
        $status = 200;

        try {
            $data = $request->getContent();
            parse_str($data, $dataArr);
            $creditCardData = [];

            $transaction = new Transaction();
            $form = $this->createForm(TransactionType::class, $transaction);
            $form->submit($dataArr);

            $manager = $this->getDoctrine()->getManager();
            var_dump($dataArr);

            $user = $this->getDoctrine()->getRepository('ApiBundle:User')->find($dataArr['user_id']);
            $service = $this->getDoctrine()->getRepository('ApiBundle:Service')->find($dataArr['service_id']);

            if (empty($user)) {
                throw new Exception('User Invalid');
            }

            if (empty($service)) {
                throw new Exception('Service Invalid');
            }

            if (empty($dataArr['cc_number']) || empty($dataArr['cc_cvv']) || empty($dataArr['cc_expiration_date']) || empty($dataArr['cc_name']) ) {
                throw new Exception('Empty Credit Card Information');
            }

            $creditCardData = [
                'cc_number' => $dataArr['cc_number'],
                'cc_cvv' => $dataArr['cc_cvv'],
                'cc_expiration_date' => $dataArr['cc_expiration_date'],
                'cc_name' => $dataArr['cc_name'],
            ];

            //Check if user has pagameId
            if(empty($user->getPagarmeId())) {
                $pagarMeService->createCustomer($user);
            }

            $transaction->setUser($user);
            $transaction->setService($service);
            $currentDate = new \DateTime('now', new \DateTimeZone("America/Sao_Paulo"));
            $transaction->setCreatedAt($currentDate);
            $transaction->setPaymentMethod('credit_card');

            $manager->persist($transaction);
            $manager->flush();

            $pagarMeService->createTransaction($transaction, $creditCardData);

            $response = [
                'success' => true,
                'message' => 'Transaction created with success'
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
     * @Route("/antifraud/{id}", methods={"GET"})
     * @param $id
     * @return JsonResponse
     *
     * @SWG\Get(
     *     path="api/transactions/antifraud/{id}",
     *     tags={"transactions"},
     *     operationId="antifraudAnalyses",
     *     description="return the result from pagar.me antifraud analyses",
     *     @SWG\Parameter(
     *         description="Transaction id to get",
     *         in="path",
     *         name="transactionId",
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
    public function antifraudAnalysesAction($id, PagarMeService $pagarMeService)
    {
        $response = [
            "success" => false,
            "message" => null
        ];
        $status = 200;

        try {
            $doctrine = $this->getDoctrine();
            $transaction = $this->getDoctrine()->getRepository("ApiBundle:Transaction")->find($id);

            if ($transaction && !empty($transaction->getPagarmeId())) {
                return new JsonResponse($pagarMeService->getAntifraudAnalyses($transaction->getPagarmeId()));
            }

            $response['message'] = "Transaction not found or transaction not proccess in pagarMe";
        }
        catch (\Exception $e) {
            $response['message'] = $e->getMessage();
            $status = 500;
        }

        return new JsonResponse($response, $status);
    }

}
