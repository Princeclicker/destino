<?php

declare(strict_types=1);

namespace Spip\Afficher\Minipage;

class Admin
{
	/**
	 * @param array<string,mixed> $params
	 */
	public function installDebutPage(array $params = []): string {
		return '';
	}

	/**
	 * @param array<string,mixed> $options
	 */
	public function page(string $corps, array $options = []): string {
		return '';
	}

	public function installFinPage(): string {
		return '';
	}
}
