<?php

namespace ApiBundle\DataFixtures;

use ApiBundle\Entity\Service;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/*
 * Insert the defaults services
 */
class ServiceFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /* These are the standard data of the system */
        $standardInserts = [
            [
                'title'       => 'Contador Online',
                'description' => 'Emita notas fiscais e faça seu IR do MEI',
                'price'       => 59.90,
                'recurrence'  => 'yearly',
                'timeToPay'   => 12
            ],
            [
                'title'       => 'Regularize sua MEI',
                'description' => 'Faça alterações cadastrais de acordo com sua necessidade',
                'price'       => 30,
                'recurrence'  => 'one time',
                'timeToPay'   => 1
            ]
        ];

        foreach ($standardInserts as $service) {
            if (empty($manager->getRepository('ApiBundle\Entity\Service')->findOneBy(['title' => $service['title']]))) {
                $serviceObj = new Service();
                $serviceObj->setTitle($service['title']);
                $serviceObj->setDescription($service['description']);
                $serviceObj->setPrice($service['price']);
                $serviceObj->setRecurrence($service['recurrence']);
                $serviceObj->setTimeToPay($service['timeToPay']);

                $manager->persist($serviceObj);
            }
        }

        $manager->flush();
    }
}