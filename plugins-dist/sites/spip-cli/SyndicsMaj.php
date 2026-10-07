<?php

namespace Spip\Cli\Command;

use Spip\Cli\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyndicsMaj extends Command
{
	protected function configure(): void {
		$this->setName('syndics:maj')
			->setDescription('Mettre à jour les flux des sites syndiqués')
			->addOption('id', null, InputOption::VALUE_OPTIONAL, 'id du site à mettre à jour')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {

		$this->demarrerSpip();
		$this->io->title('Mise à jour des flux RSS');

		include_spip('genie/syndic');

		if ($input->getOption('id')) {
			$this->io->care('Mise à jour RSS site ' . $input->getOption('id'));
			syndic_a_jour($input->getOption('id'));
			$this->io->check('done');
		} else {
			$syndics = sql_allfetsel('id_syndic', 'spip_syndic', 'syndication = "oui"');
			foreach (array_column($syndics, 'id_syndic') as $id_syndic) {
				$this->io->care('Mise à jour RSS site ' . $id_syndic);
				syndic_a_jour($id_syndic);
				$this->io->check('done');
			}
		}

		return Command::SUCCESS;
	}
}
