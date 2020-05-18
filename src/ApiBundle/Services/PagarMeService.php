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
        $route = 'zipcodes/';
        $url = $this->endpointBase.$route.$zipcode;

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

    public function createTransaction(Transaction $transaction)
    {

    }

}