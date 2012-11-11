<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  InfinitySoft 2011
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    phpless
 * @license    LGPL
 * @filesource
 */


# include lessc
require_once(TL_ROOT . '/system/modules/phpless/lessc.inc.php');


/**
 * Class PHPLessCss
 *
 * wrapper class for the less css compiler (http://lesscss.org)
 */
class PHPLessCss extends AbstractMinimizer
{
	/**
	 * The import search directory.
	 */
	protected $strImportDir = '';
	
	
	/**
	 * load the jsmin class
	 */
	public function  __construct()
	{
		parent::__construct();
		
		$this->import('CssUrlRemapper');
	}
	
	
	/**
	 * Set the import search directory.
	 */
	public function setImportDir($strImportDir)
	{
		$this->strImportDir = $strImportDir;
	}
	
	
	/**
	 * Get the import search directory.
	 */
	public function getImportDir()
	{
		return $this->strImportDir;
	}

	
	/**
	 * Create a temporary file and return a contao relative path.
	 * 
	 * @return string
	 */
	private function createTempFile()
	{
		return substr(tempnam(TL_ROOT . '/system/html', 'PHPLessCss_'), strlen(TL_ROOT)+1);
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Minimizer::minimize($strSource, $strTarget)
	 */
	public function minimize($strSource, $strTarget)
	{
		try {
            $objSource = new File($strSource);
            $strCode = $objSource->getContent();
            $objSource->close();

			$strCode = $this->minimizeCode($strCode, $strSource);

            $objTarget = new File($strTarget);
            $objTarget->write($strCode);
            $objTarget->close();
		} catch(Exception $e) {
			$this->log($e->getMessage(), 'PHPLessCss::minimize', TL_ERROR);
			return false;
		}
		return true;
	}


	/**
	 * (non-PHPdoc)
	 * @see Minimizer::minimizeFromFile($strFile)
	 */
	public function minimizeFromFile($strFile)
	{
		try {
            $objSource = new File($strFile);
            $strCode = $objSource->getContent();
            $objSource->close();

			return $this->minimizeCode($strCode, $strFile);
		} catch(Exception $e) {
			$this->log($e->getMessage(), 'PHPLessCss::minimize', TL_ERROR);
			return false;
		}
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Minimizer::minimizeToFile($strFile, $strCode)
	 */
	public function minimizeToFile($strFile, $strCode)
	{
		try {
			$strCode = $this->minimizeCode($strCode);
			
			$objFile = new File($strFile);
			$objFile->write($strCode);
			$objFile->close();
		} catch(Exception $e) {
			$this->log($e->getMessage(), 'PHPLessCss::minimize', TL_ERROR);
			return false;
		}
		return true;
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Minimizer::minimizeCode($strCode)
	 */
	public function minimizeCode($strCode, $strSource = null)
	{
		try {
			$objLessC = new lessc();
			if ($this->strImportDir)
			{
				$objLessC->importDir = TL_ROOT . '/' . $this->strImportDir;
			}
			$strCode = $objLessC->compile($strCode);

            // HOOK: add custom logic
            if (isset($GLOBALS['TL_HOOKS']['compileLess']) && is_array($GLOBALS['TL_HOOKS']['compileLess']))
            {
                foreach ($GLOBALS['TL_HOOKS']['compileLess'] as $callback)
                {
                    $this->import($callback[0]);
                    $strCode = $this->$callback[0]->$callback[1]($strCode, $strSource);
                }
            }

            return $strCode;
		} catch(Exception $e) {
			$this->log($e->getMessage(), 'PHPLessCss::minimize', TL_ERROR);
			return false;
		}
	}
}

?>