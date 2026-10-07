<?php

/**
 * SPIP, Système de publication pour l'internet
 *
 * Copyright © avec tendresse depuis 2001
 * Arnaud Martin, Antoine Pitrou, Philippe Rivière, Emmanuel Saint-James
 *
 * Ce programme est un logiciel libre distribué sous licence GNU/GPL.
 */

namespace Spip\Core\Tests;

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * CompresseurMinifierCssTest test : teste la minification CSS
 */
#[CoversFunction('minifier_css')]
class CompresseurMinifierCssTest extends TestCase
{
	public static function setUpBeforeClass(): void {
		include_spip('inc/compresseur_minifier');
	}

	public static function providerSimpleAll() {
		return self::provideTestsFor('simple', 'all');
	}

	public static function providerSimpleScreen() {
		return self::provideTestsFor('simple', 'screen');
	}

	#[DataProvider('providerSimpleAll')]
	public function testSimpleDefault($source, $expected) {
		$mini = \minifier_css($source);
		$this->assertEquals($expected, $mini);
	}

	#[DataProvider('providerSimpleAll')]
	public function testSimpleAll($source, $expected) {
		// media all doit donner le meme resultat que par defaut
		$mini = \minifier_css($source, 'all');
		$this->assertEquals($expected, $mini);
	}

	#[DataProvider('providerSimpleScreen')]
	public function testSimpleScreen($source, $expected) {
		$mini = \minifier_css($source, 'screen');
		$this->assertEquals($expected, $mini);
	}

	public static function providerCsstidyAllHigh() {
		return self::provideTestsFor('csstidy', 'all', 'high');
	}

	public static function providerCsstidyScreenHigh() {
		return self::provideTestsFor('csstidy', 'screen', 'high');
	}

	public static function providerCsstidyScreenHighest() {
		return self::provideTestsFor('csstidy', 'screen', 'highest');
	}

	#[DataProvider('providerCsstidyAllHigh')]
	public function testCsstidyDefault($source, $expected) {
		// media all doit donner le meme resultat que par defaut
		$mini = \minifier_css($source, []);
		$this->assertEquals($expected, $mini);
	}

	#[DataProvider('providerCsstidyAllHigh')]
	public function testCsstidyDefaultHigh($source, $expected) {
		// media all doit donner le meme resultat que par defaut
		$mini = \minifier_css($source, ['template' => 'high']);
		$this->assertEquals($expected, $mini);
	}

	#[DataProvider('providerCsstidyAllHigh')]
	public function testCsstidyAll($source, $expected) {
		// media all doit donner le meme resultat que par defaut
		$mini = \minifier_css($source, ['media' => 'all']);
		$this->assertEquals($expected, $mini);
	}

	#[DataProvider('providerCsstidyAllHigh')]
	public function testCsstidyAllHigh($source, $expected) {
		// media all doit donner le meme resultat que par defaut
		$mini = \minifier_css($source, ['media' => 'all', 'template' => 'high']);
		$this->assertEquals($expected, $mini);
	}

	#[DataProvider('providerCsstidyScreenHigh')]
	public function testCsstidyScreen($source, $expected) {
		// media all doit donner le meme resultat que par defaut
		$mini = \minifier_css($source, ['media' => 'screen']);
		$this->assertEquals($expected, $mini);
	}

	#[DataProvider('providerCsstidyScreenHigh')]
	public function testCsstidyScreenHigh($source, $expected) {
		// media all doit donner le meme resultat que par defaut
		$mini = \minifier_css($source, ['media' => 'screen', 'template' => 'high']);
		$this->assertEquals($expected, $mini);
	}

	#[DataProvider('providerCsstidyScreenHighest')]
	public function testCsstidyScreenHighest($source, $expected) {
		// media all doit donner le meme resultat que par defaut
		$mini = \minifier_css($source, ['media' => 'screen', 'template' => 'highest']);
		$this->assertEquals($expected, $mini);
	}

	protected static function provideTestsFor($which, $media, $level = '') {
		$data = [];

		$dirSource = __DIR__ . "/data/minifier_css/$which/source/";
		$dirExpected = __DIR__ . "/data/minifier_css/$which/expected/$media/";
		if ($level) {
			$dirExpected .= "$level/";
		}

		$sourceFiles = glob($dirSource . '*.css');
		foreach ($sourceFiles as $sourceFile) {
			$name = basename($sourceFile);
			$expectedFile = $dirExpected . $name;
			if (file_exists($expectedFile)) {
				$source = file_get_contents($sourceFile);
				// on rtrim expected car les editeurs ajoutent parfois un saut de ligne final
				$expected = rtrim(file_get_contents($expectedFile));
				$data["$which:$name"] = [$source, $expected];
			}
		}

		return $data;
	}
}
