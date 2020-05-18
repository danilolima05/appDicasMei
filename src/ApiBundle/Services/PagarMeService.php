<?php


namespace ApiBundle\Services;

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

    public function getAddressByZipCode(string $zipcode)
    {
        $route = 'zipcodes/';
        $url = $this->endpointBase.$route.$zipcode;

        return $this->apiService->consumeAPI('GET', $url, ['api_key' => $this->apiKey]);
    }
}