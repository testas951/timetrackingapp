<?php

namespace App\DataFixtures;

use App\Entity\Times;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class AppFixtures extends Fixture
{
    //php bin/console doctrine:fixtures:load --purge-with-truncate
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i < 200; $i++) {
            $time = new Times();
            $time->setUserId(rand(1,5));
            $time->setTitle('test title ' . $i);
            $time->setComment('test comment ' . $i);
            //$time->setDate(date('Y-m-d',strtotime('2021-0' . rand(1,3) . '-' . rand(1,29))));
            $time->setDate(DateTime::createFromFormat('Y-m-d', '2021-0' . rand(1,3) . '-' . rand(1,29)));
            $time->setTime(rand(1,150));

            $manager->persist($time);
            $manager->flush();
        }
    }
}
