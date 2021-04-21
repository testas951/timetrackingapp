<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {

        for ($i = 1; $i < 6; $i++) {
            $user = new User();
            $user->setEmail('test_' . $i . '@test.lt');
            $user->setPassword(
                $this->encoder->encodePassword($user, '123456')
            );

            $manager->persist($user);
            $manager->flush();
        }

    }
}
