<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoderInterface)
    {
        $this->encoder = $userPasswordEncoderInterface;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('admin@mail.com');
        $user->setPassword($this->encoder->encodePassword($user, 'admin'));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setCreatedAt(new DateTime());
        $manager->persist($user);

        $user = new User();
        $user->setEmail('user@mail.com');
        $user->setPassword($this->encoder->encodePassword($user, 'user'));
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new DateTime());
        $manager->persist($user);

        $manager->flush();
    }
}
