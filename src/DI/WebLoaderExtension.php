<?php
/**
 * WebLoaderExtension.php
 *
 * @author Michal Pospiech <michal@pospiech.cz>
 */

namespace mpospiech\WebLoader\DI;


use mpospiech\WebLoader\WebLoader;
use Nette\DI\CompilerExtension;
use WebLoader\Filter\CssUrlsFilter;
use WebLoader\Filter\LessFilter;

class WebLoaderExtension extends CompilerExtension
{

	private function getDefaultConfig()
	{
		return [
			'sourceDir' => 'design',
			'outputDir' => 'cssjstemp',
			'cssUrlsFilter' => false,
			'lessFilter' => false
		];
	}

	public function loadConfiguration()
	{
		$config = $this->getConfig($this->getDefaultConfig());
		$builder = $this->getContainerBuilder();

		$wl = $builder->addDefinition($this->prefix('webLoader'))
			->setClass(WebLoader::class, ['%wwwDir%', $config['sourceDir']])
			->addSetup('setOutputDir', [$config['outputDir']]);

		if ($config['cssUrlsFilter']) {
			$builder->addDefinition($this->prefix('cssUrlsFilter'))
				->setClass(CssUrlsFilter::class, ['%wwwDir%']);

			$wl->addSetup('setCssUrlsFilter');
		}

		if ($config['lessFilter']) {
			$builder->addDefinition($this->prefix('lessFilter'))
				->setClass(LessFilter::class);

			$wl->addSetup('setLessFilter');
		}
	}

}