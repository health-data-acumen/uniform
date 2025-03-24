<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user:create',
    description: 'Create a new user',
)]
class UserCreateCommand extends Command
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'User email')
            ->addOption('full-name', null, InputOption::VALUE_OPTIONAL, 'User full name')
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'User password')
            ->addOption('admin', null, InputOption::VALUE_OPTIONAL, 'Set the user as admin')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);

        if (!($email = $input->getOption('email'))) {
            $email = $io->ask('Email', validator: function (mixed $value): string {
                if (null === $value) {
                    throw new \InvalidArgumentException('The email cannot be empty.');
                }

                return $value;
            });
        }

        if (!($password = $input->getOption('password'))) {
            $password = $io->askHidden('Password', validator: function (mixed $value): string {
                if (null === $value) {
                    throw new \InvalidArgumentException('The password cannot be empty.');
                }

                if (strlen($value) < 8) {
                    throw new \InvalidArgumentException('The password must be at least 8 characters long.');
                }

                return $value;
            });
        }

        if (!($fullName = $input->getOption('full-name'))) {
            $fullName = $io->ask('Full name', validator: function (mixed $value): string {
                if (null === $value) {
                    throw new \InvalidArgumentException('The full name cannot be empty.');
                }

                return $value;
            });
        }

        $input->setOption('email', $email);
        $input->setOption('password', $password);
        $input->setOption('full-name', $fullName);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = (new User())
            ->setEmail($input->getOption('email'))
            ->setFullName($input->getOption('full-name'))
            ->setRoles($input->getOption('admin') ? ['ROLE_ADMIN'] : ['ROLE_USER'])
        ;

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $input->getOption('password'))
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('User created successfully.');

        return Command::SUCCESS;
    }
}
