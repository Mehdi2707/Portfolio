<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker;

class UsersFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher, private SluggerInterface $slugger)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@demo.fr');
        $admin->setLastname('BRBT');
        $admin->setFirstname('MHD');
        $admin->setAddress('12 rue de la rue');
        $admin->setZipcode('75000');
        $admin->setCity('Paris');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin'));
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        $faker = Faker\Factory::create('fr_FR');

        for($usr = 1 ; $usr <= 5 ; $usr++)
        {
            $user = new Users();
            $user->setEmail($faker->email);
            $user->setLastname($faker->lastName);
            $user->setFirstname($faker->firstName);
            $user->setAddress($faker->streetAddress);
            $user->setZipcode(str_replace(' ', '', $faker->postcode));
            $user->setCity($faker->city);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'secret'));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
