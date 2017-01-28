<?php
/**
 * WebLoader.php
 *
 * @author Michal Pospiech <michal@pospiech.cz>
 */

namespace mpospiech\WebLoader;


use Nette\Http\IRequest;
use Nette\Object;
use WebLoader\Compiler;
use WebLoader\FileCollection;
use WebLoader\Filter\CssUrlsFilter;
use WebLoader\Filter\LessFilter;
use WebLoader\Nette\CssLoader;
use WebLoader\Nette\JavaScriptLoader;

class WebLoader extends Object implements IWebLoader
{

	/** @var string */
	private $wwwDir;

	/** @var string */
	private $sourceDir;

	/** @var string */
	private $outputDir;

	/** @var array */
	private $files = [];

	/** @var array */
	private $filesAfterPresenter = [];

	/** @var string */
	private $root;

	/** @var IRequest */
	private $httpRequest;

	/** @var CssUrlsFilter */
	private $cssUrlsFilter;

	/** @var bool */
	private $lessFilter = false;

	public function __construct($wwwDir, $sourceDir, IRequest $httpRequest)
	{
		$this->wwwDir = $wwwDir;
		$this->sourceDir = $sourceDir;
		$this->httpRequest = $httpRequest;

		$this->root = $wwwDir . DIRECTORY_SEPARATOR . $sourceDir;
	}

	public function setOutputDir($outputDir)
	{
		$this->outputDir = $outputDir;
	}

	public function setCssUrlsFilter(CssUrlsFilter $cssUrlsFilter)
	{
		$this->cssUrlsFilter = $cssUrlsFilter;
	}

	public function setLessFilter($isLessFilter = true)
	{
		$this->lessFilter = $isLessFilter;
	}

	/**
	 * @param string $file
	 * @param bool $beforeScriptsFromPresenter
	 * @return self
	 */
	public function addFile($file, $beforeScriptsFromPresenter = true)
	{
		if ($beforeScriptsFromPresenter) {
			$this->files[$file] = $file;
		} else {
			$this->filesAfterPresenter[$file] = $file;
		}

		return $this;
	}

	/**
	 * @param $file
	 * @return self
	 */
	public function addRemoteFile($file)
	{
		return $this->addFile($file);
	}

	/**
	 * @param string $file
	 * @return self
	 */
	public function removeFile($file)
	{
		if (array_key_exists($file, $this->files)) {
			unset($this->files[$file]);
		}

		if (array_key_exists($file, $this->filesAfterPresenter)) {
			unset($this->filesAfterPresenter[$file]);
		}

		return $this;
	}

	/**
	 * @return CssLoader
	 */
	public function createCssLoader()
	{
		$fileCollection = $this->createFileCollection(array_filter($this->files, [$this, 'isCss'] + array_filter($this->filesAfterPresenter, [$this, 'isCss'])));
		$compiler = Compiler::createCssCompiler($fileCollection, $this->wwwDir . DIRECTORY_SEPARATOR . $this->outputDir);
		if ($this->cssUrlsFilter) {
			$compiler->addFileFilter($this->cssUrlsFilter);
		}
		if ($this->lessFilter) {
			$compiler->addFileFilter(new LessFilter());
		}

		return new CssLoader($compiler, $this->httpRequest->getUrl()->getBasePath() . $this->outputDir);
	}

	/**
	 * @return JavaScriptLoader
	 */
	public function createJavascriptLoader()
	{
		$fileCollection = $this->createFileCollection(array_filter($this->files, [$this, 'isJs']) + array_filter($this->filesAfterPresenter, [$this, 'isJs']));
		$compiler = Compiler::createJsCompiler($fileCollection, $this->wwwDir . DIRECTORY_SEPARATOR . $this->outputDir);
		return new JavaScriptLoader($compiler, $this->httpRequest->getUrl()->getBasePath() . $this->outputDir);
	}

	/**
	 * @param array $files
	 * @return FileCollection
	 */
	private function createFileCollection(array $files)
	{
		$fileCollection = new FileCollection($this->root);
		foreach ($files as $file) {
			if ($this->isRemoteFile($file)) {
				$fileCollection->addRemoteFile($file);
			} else {
				$fileCollection->addFile($file);
			}
		}

		return $fileCollection;
	}

	/**
	 * @param string $file
	 * @return bool
	 */
	private function isRemoteFile($file)
	{
		return (filter_var($file, FILTER_VALIDATE_URL) or strpos($file, '//') === 0);
	}

	/**
	 * @param string $file
	 * @return int
	 */
	private function isCss($file)
	{
		return preg_match('~\.css|\.less$~', $file);
	}

	/**
	 * @param string $file
	 * @return int
	 */
	private function isJs($file)
	{
		return preg_match('~\.js~', $file);
	}

}