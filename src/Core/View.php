<?php
/**
 *
 * @View Class
 *
 */

namespace TinyFw\Core;

class View
{	
	public $_disableLayout = false;	
	public $_defaultLayoutPath = "default";
	
	/**
	 *
	 * The constructor, duh
	 * @var string
	 */
	public function __construct($defaultLayoutSubPath = "default")
	{
		$this->_defaultLayoutPath = $defaultLayoutSubPath;
	}

	/**
	 * The variable property contains the variables
	 * that can be used inside of the templates.
	 *
	 * @access private
	 * @var array
	 */
	private $variables = array();

	/**
	 * The directory where the templates are stored
	 *
	 * @access private
	 * @var string
	 */
	private $templateDir = null;
	
	/**
	 * The directory where the templates are stored
	 *
	 * @access private
	 * @var string
	 */
	private $layoutDir = null;

    /**
     * The content html after render layout
     *
     * @access private
     * @var string
     */
    private $contentHtml = null;

    /**
	 * Adds a variable that can be used by the templates.
	 * Adds a new array index to the variable property. This
	 * new array index will be treated as a variable by the templates.
	 * @param string $name The variable name to use in the template
	 * @param string $value The content you assign to $name
	 * @access public
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->variables[$name] = $value;
	}

	/**
	 * Set variables for view
	 * added variables.
	 * @param array $args
	 * @access public
	 * @return void
	 */
	public function setVars($args = array())
	{
		if(!empty($args))
			$this->variables = array_merge($this->variables,$args);
	}

	/**
	 * Returns a value of variable in View
	 * @access public
	 * @return string | object
	 */
	public function getVars($name)
	{
        if (isset($this->variables[$name]))
            return $this->variables[$name];
        return null;
	}

    /**
     * Returns a numeral array containing the names of all
     * added variables.
     * @access public
     * @return array
     */
    public function getAllNamingVars()
    {
        $variables = array_keys($this->variables);
        return !empty($variables) ? $variables : false;
    }


    /**
     * Set template dir
     * @access public
     * @param string $templateDir
     * @return none
     */
    public function setTemplateDir($templateDir)
    {
        $this->templateDir = $templateDir;
    }

    /**
     * Set layout dir
     * @access public
     * @param string $layoutDir
     * @return none
     */
    public function setLayoutDir($layoutDir)
    {
        $this->layoutDir = $layoutDir;
    }

    /**
     * Get layout dir
     * @access public
     * @return string
     */
    public function getLayoutDir()
    {
        return $this->layoutDir;
    }

    /**
     * Get layout path
     * @access public
     * @return string
     */
    public function getLayoutPath()
    {
        return $this->_defaultLayoutPath;

    }

    /**
     * Set layout path
     * @access public
     * @param string $layoutPath
     * @return none
     */
    public function setLayoutPath($layoutPath)
    {
        $this->_defaultLayoutPath = $layoutPath;
    }

    /**
     * Set status layout
     * @access public
     * @param bool $bool
     * @return none
     */
    public function setEnableLayout($bool)
    {
        $this->_disableLayout = $bool;
    }

    /**
     * Get content html
     * @access public
     * @return string
     */
    public function getContent()
    {
        return $this->contentHtml;
    }

    /**
     * Set content html
     * @access public
     * @param string $content
     * @return none
     */
    public function setContent($content)
    {
        $this->contentHtml = $content;
    }

    /**
     * @Returns Outputs the final template output
     * Fetches the final template output, and echoes it to the browser.
     *
     * Returns a numeral array containing the names of all
     * added variables.
     * @access public
     * @param string $full_path Filename (with path) to the template you want to output
     * @param array $args
     * @throws \Exception
     * @return null
     */
	public function parser($full_path,$args = array())
	{
		if (file_exists($full_path) == false)
		{
			throw new \Exception('View not found in '.$full_path);
		}
		
		$output = $this->getOutput($full_path,$args);
		return isset($output) ? $output : false;		
	}
	
	public function fetch($path,$args = array()) 
	{
		$path = $this->templateDir . '/' . $path . '.phtml';
			
		if (file_exists($path) == false)
		{
			throw new \Exception('View not found in '.$path);
		}

        $variables = $this->variables;
        if(!empty($args))
            $variables = array_merge($variables,$args);

        $output = $this->getOutput($path,$variables);
		return isset($output) ? $output : false;
	}
	
	public function renderLayout($content_path, $variables = null, $layout_path = null)
	{
	    if (!empty($variables))
	        $this->setVars($variables);

        $this->variables['main_content'] = $this->fetch($content_path);

        if($this->_disableLayout == true)
        {
            $this->contentHtml = $this->variables['main_content'];
            return $this->contentHtml;
        }

		if(is_null($layout_path))
            $layout_path = $this->layoutDir."/{$this->_defaultLayoutPath}.phtml";
		else
            $layout_path = $this->layoutDir.'/'.$layout_path.'.phtml';
			
		if (file_exists($layout_path) == false)
		{
			throw new \Exception('Layout not found in '.$layout_path);
		}

        $this->contentHtml = $this->getOutput($layout_path,$this->variables);
		return isset($this->contentHtml) ? $this->contentHtml : false;
    }

	/**
	 * @Returns Fetch the template output, and return it
	 * @param string $template_file Filename (with path) to the template to be processed
     * @param array() $args
     * @throws \Exception
	 * @return string Returns a string on success, and FALSE on failure
	 * @access private
	 */
	private function getOutput($template_file,$args = array())
	{
		if (file_exists($template_file))
		{
            /*** extract all the variables ***/
            extract($args);

			ob_start();
			include($template_file);
			$output = ob_get_contents();
			ob_end_clean();
		}
		else
		{
			throw new \Exception("The template file '$template_file' does not exist");
		}
		return !empty($output) ? $output : false;
	}

} /*** end of class ***/

?>
