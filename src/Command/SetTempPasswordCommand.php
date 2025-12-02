<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:set-temp-password',
    description: 'Définir un mot de passe provisoire pour un compte (force le changement à la prochaine connexion)',
)]
class SetTempPasswordCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $io->ask('Email du compte');
        if (!$email) {
            $io->error('Email requis.');
            return Command::FAILURE;
        }

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => mb_strtolower($email)]);
        if (!$user) {
            $io->error('Aucun utilisateur avec cet email.');
            return Command::FAILURE;
        }

        $temp = bin2hex(random_bytes(4)); // 8 hex chars
        $user->setPassword($this->passwordHasher->hashPassword($user, $temp));
        $user->setMustChangePassword(true);
        $this->userRepository->save($user, true);

        $io->success(sprintf('Mot de passe provisoire défini pour %s : %s', $email, $temp));
        $io->note('Il sera demandé de le changer dès la prochaine connexion.');

        return Command::SUCCESS;
    }
}
