<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:promote-admin',
    description: 'Ajoute le rôle ROLE_ADMIN à un compte existant',
)]
class PromoteAdminCommand extends Command
{
    public function __construct(private readonly UserRepository $userRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $io->ask('Email du compte à promouvoir');
        if (!$email) {
            $io->error('Email requis.');
            return Command::FAILURE;
        }

        $user = $this->userRepository->findOneBy(['email' => mb_strtolower($email)]);
        if (!$user) {
            $io->error('Aucun utilisateur trouvé avec cet email.');
            return Command::FAILURE;
        }

        $roles = $user->getRoles();
        if (!in_array('ROLE_ADMIN', $roles, true)) {
            $roles[] = 'ROLE_ADMIN';
            $user->setRoles($roles);
            $this->userRepository->save($user, true);
            $io->success(sprintf('Le rôle ROLE_ADMIN a été ajouté à %s.', $email));
        } else {
            $io->note(sprintf('%s possède déjà ROLE_ADMIN.', $email));
        }

        $io->comment('Si le mot de passe est inconnu, lancez ensuite "php bin/console app:set-temp-password" pour définir un mot de passe provisoire.');

        return Command::SUCCESS;
    }
}
