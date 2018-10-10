<?php
/**
 * IWebLoader.php
 *
 * @author Michal Pospiech <michal@pospiech.cz>
 */

namespace mpospiech\WebLoader;


use WebLoader\Nette\CssLoader;
use WebLoader\Nette\JavaScriptLoader;

interface IWebLoader
{

	/**
	 * @return CssLoader
	 */
	function createCssLoader();

	/**
	 * @return JavaScriptLoader
	 */
	function createJavascriptLoader();

}