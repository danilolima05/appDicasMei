<?php


namespace ApiBundle\Services;

use ApiBundle\Entity\Transaction;
use ApiBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\ManagerConfigurator;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectManagerDecorator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PagarMeService
{
    /**
     * @var string
     */
    private $apiKey = null;

    /**
     * @var string
     */
    private $apiSecret = null;

    /**
     * @var string
     */
    private $endpointBase = 'https://api.pagar.me/1/';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ApiService
     */
    private $apiService;

    /**
     * PagarMeService constructor.
     * @param ContainerInterface $container
     * @param ApiService $apiService
     */
    public function __construct(ContainerInterface $container, ApiService $apiService)
    {
        $this->container = $container;
        $this->apiService = $apiService;

        if (!empty($container->hasParameter('pagarme_api_key')))
            $this->apiKey = $container->getParameter('pagarme_api_key');

        if (!empty($container->hasParameter('pagarme_api_secret')))
            $this->apiSecret = $container->getParameter('pagarme_api_secret');
    }

    /**
     * @param string $zipcode
     * @return mixed
     */
    public function getAddressByZipCode(string $zipcode)
    {
        $route = 'zipcodes/'.$zipcode;
        $url = $this->endpointBase.$route;

        return $this->apiService->consumeAPI('GET', $url, ['api_key' => $this->apiKey]);
    }

    /**
     * @param $transactionId
     * @return mixed
     */
    public function getAntifraudAnalyses($transactionId)
    {
        $route = 'transactions/'.$transactionId.'/antifraud_analyses';
        $url = $this->endpointBase.$route;

        return $this->apiService->consumeAPI('GET', $url, ['api_key' => $this->apiKey]);
    }

    /**
     * @param User $user
     * @return false|string
     */
    public function createCustomer(User $user)
    {
        if(empty($user)){
            return json_encode(['success' => false, 'response' => 'Invalid user']);
        }

        if(!empty($user->getPagarmeId())){
            return json_encode(['success' => false, 'response' => 'User already registered in PagarMe']);
        }

        try {
            $data = [
                'api_key' => $this->apiKey,
                "external_id" =>  "{$user->getId()}",
                "name" =>  $user->getName(),
                "type" =>  "individual",
                "country" =>  strtolower($user->getCountry()),
                "email" =>  $user->getEmail(),
                "documents" => [
                    (object) [
                        "type" => "cpf",
                        "number" => $user->getCpf()
                    ]
                ],
                "phone_numbers" => [
                      $user->getPhone()
                ]
            ];

            $route = 'customers';
            $url = $this->endpointBase.$route;

            $response = $this->apiService->consumeAPI('POST', $url, json_encode($data));

            if (!empty($response->id)) {
                $user->setPagarmeId($response->id);
                $user->setUpdatedAt(new \DateTime('now', new \DateTimeZone("America/Sao_Paulo")));

                $manager = $this->container->get('doctrine')->getManager();
                $manager->persist($user);
                $manager->flush();
            }

            return json_encode(['success' => true, 'response' => $response]);

        } catch (\Exception $e) {
            return json_encode(['success' => false, 'response' => $e->getMessage()]);
        }
    }

    public function createTransaction(Transaction $transaction, $cardNumberInformation = [])
    {
        try {
            $data = [
                "api_key" => $this->apiKey,
                "amount" => $transaction->getService()->getPrice() * 100,
                "card_number" => $cardNumberInformation['cc_number'],
                "card_cvv" => $cardNumberInformation['cc_cvv'],
                "card_expiration_date" => $cardNumberInformation['cc_expiration_date'],
                "card_holder_name" => $cardNumberInformation['cc_name'],
                "customer" =>
                    [
                        "external_id" => (string)$transaction->getUser()->getId(),
                        "name" => $transaction->getUser()->getName(),
                        "type" => "individual",
                        "country" => "br",
                        "email" => $transaction->getUser()->getEmail(),
                        "documents" => [
                            (object)[
                                "type" => "cpf",
                                "number" => $transaction->getUser()->getCpf()
                            ]
                        ],
                        "phone_numbers" => [$transaction->getUser()->getPhone()],
                        "birthday" => "1965-01-01"
                    ],
                "billing" => [
                    "name" => $transaction->getUser()->getName(),
                    "address" => [
                        "country" => "br",
                        "state" => $transaction->getUser()->getState(),
                        "city" => $transaction->getUser()->getCity(),
                        "neighborhood" => $transaction->getUser()->getNeighborhood(),
                        "street" => $transaction->getUser()->getAddress(),
                        "street_number" => "9999",
                        "zipcode" => $transaction->getUser()->getCep()
                    ]
                ],
                "items" => [
                    (object)[
                        "id" => (string)$transaction->getService()->getId(),
                        "title" => $transaction->getService()->getTitle(),
                        "unit_price" => $transaction->getService()->getPrice() * 100,
                        "quantity" => 1,
                        "tangible" => false
                    ]
                ]
            ];

            $route = 'transactions';
            $url = $this->endpointBase.$route;

            $response = $this->apiService->consumeAPI('POST', $url, json_encode($data));

            if (!empty($response->status)) {
                $transaction->setStatus($response->status);
                $transaction->setPagarmeId($response->tid);

                $manager = $this->container->get('doctrine')->getManager();
                $manager->persist($transaction);
                $manager->flush();
            }

            return json_encode(['success' => true, 'response' => $response]);

        } catch (\Exception $e) {
            return json_encode(['success' => false, 'response' => $e->getMessage()]);
        }
    }
}