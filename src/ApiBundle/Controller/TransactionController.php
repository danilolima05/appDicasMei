<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\Transaction;
use ApiBundle\Entity\Service;
use ApiBundle\Entity\User;
use ApiBundle\Form\TransactionType;
use ApiBundle\Services\PagarMeService;
use http\Exception\RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/transactions")
 */
class TransactionController extends Controller
{
    /**
     * @Route("/", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAction(Request $request, PagarMeService $pagarMeService)
    {

        $response = [
            'success' => false,
            'message' => null
        ];

        try {
            $data = $request->getContent();
            parse_str($data, $dataArr);

            $transaction = new Transaction();
            $form = $this->createForm(TransactionType::class, $transaction);
            $form->submit($dataArr);

            $manager = $this->getDoctrine()->getManager();

            $user = $this->getDoctrine()->getRepository('ApiBundle:User')->find($dataArr['user_id']);
            $service = $this->getDoctrine()->getRepository('ApiBundle:Service')->find($dataArr['service_id']);

            if (empty($user)) {
                throw new RuntimeException('User Invalid');
            }
            if (empty($service)) {
                throw new RuntimeException('Service Invalid');
            }

            //Check if user has pagameId
            if(empty($user->getPagarmeId())) {
                $pagarMeService->createCustomer($user);
            }

            $transaction->setUser($user);
            $transaction->setService($service);
            $currentDate = new \DateTime('now', new \DateTimeZone("America/Sao_Paulo"));
            $transaction->setCreatedAt($currentDate);

            $manager->persist($transaction);
            $manager->flush();

            $pagarMeService->createTransaction($transaction);

            $response = [
                'success' => true,
                'message' => 'Transaction created with success'
            ];
        }
        catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return new JsonResponse($response);
    }

}
