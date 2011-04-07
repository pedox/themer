<?php
/**
 * Themer
 *
 * A Tumblr theme parser for local development.
 *
 * @package   Themer
 * @author    Braden Schaeffer 
 * @version   beta
 * @link      http://github.com/bschaeffer/themer
 *
 * @copyright Copyright (c) 2011
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 *
 * @filesource
 */

use Themer\Data;
use Themer\Router;
use Themer\Parser;
use Themer\Load;
use Themer\Error;

class Themer {
  
  const VERSION = 'beta';
  
  public static $PWD  = '';
  public static $HOME = '';
  public static $theme_file = 'theme.html';
  
  private static $_theme_path = '';
  
  /**
   * Run the Themer parsing library.
   * 
   * @static
   * @access  public
   * @param   string  the project path
   * @return  void
   */
  public static function run($path = '')
  {     
    self::_init($path);
  
    // If $_GET['theme'] is set, we are loading a theme within an iframe
    // so we don't need any extra mumbo jumbo... I think
    if( ! isset($_GET['theme']))
    {
      Router::route();
    }
    
    self::parse_theme();
  }
  
  /**
   * A simple wrapper that sends the theme parser on it's way
   * 
   * @static
   * @access  public
   * @return  void
   */
  public static function parse_theme()
  {
    $theme = self::load_theme();
    $theme = Parser::parse($theme);
    Load::display_html($theme);
  }

  /**
   * Loads the theme from a specified path.
   * 
   * @static
   * @access  public
   * @return  string  the theme contents
   */
  public static function load_theme()
  {
    return file_get_contents(static::$_theme_path);
  }
  
  /**
   * Initializes Themer.
   * 
   * This function should not be called 'publicly'.
   * 
   * @access  private
   * @param   string  the project path
   * @return  void
   */
  public static function _init($path)
  {
    self::_setup_paths($path);
    self::_setup_data();
  }
  
  /**
   * Parses the project directory and the theme file path.
   *
   * @static
   * @access  private
   * @param   string  the potential file path
   * @return  void 
   */
  private static function _setup_paths($path = '')
  { 
    static::$PWD = self::_get_pwd($path);
    
    static::$_theme_path = static::$PWD.static::$theme_file;
    
    if( ! @file_exists(static::$_theme_path))
    {
      Error::display("The theme file `".static::$theme_file."` could not be found in ".static::$PWD);
    }
  }
  
  /**
   * Loads Tumblr blog data, merging it default Themer data (bundled
   * notes and reblog data files) along the way.
   * 
   * @access  private
   * @return  void
   */
  private static function _setup_data()
  {
    Data::load('defaults');
    Data::load('data');
    
    // Filter out the '?theme' portion of the GET request
    $get = array_diff_assoc($_GET, array('theme' => ''));
    
    // Let's see if there is any $_GET data left to merge
    if( ! empty($get))
    {
      $defaults = array(
        'Title'               => 'Title',
        'Description'         => 'Description',
        'MetaDescription'     => array('Description', '', 'strip_tags'),
        'Pages'               => array('Pages', array()),
        'AskEnabled'          => array('AskEnabled', FALSE),
        'AskLabel'            => 'AskLabel',
        'CustomCSS'           => 'CustomCSS',
        'SubmissionsEnabled'  => array('SubmissionsEnabled', FALSE),
        'SubmitLabel'         => 'SubmitLabel',
        'TwitterUsername'     => 'TwitterUsername',
        'per_page'            => array('per_page', 6)
      );
      
      $data = \Themer\Tumblr\Templatize::templatize_with($defaults, $get);
    
      Data::merge_with($data);
    }
    
    Parser::$data = Data::get();
  }
  
  /**
   * Attempts to get the current working directory
   * 
   * @static
   * @access  private
   * @param   string  the potential PWD
   * @return  string  the valid PWD
   */
  private static function _get_pwd($path)
  {
    if(empty($path))
    {
      if( ! empty(static::$PWD))
      {
        $path = static::$PWD;
      }
      elseif(isset($_SERVER['PWD']))
      {
        $path = $_SERVER['PWD'];
      }
      elseif(isset($_SERVER['DOCUMENT_ROOT']))
      {
        $path = $_SERVER['DOCUMENT_ROOT'];
      }
      elseif( ! ($path = getcwd()))
      {
        Error::display('Cannot discover the current working directory in the current environment.');
      }
    }

    return rtrim($path, '/').'/';
  }
}

/* End of file base.php */
/* Location: ./themer/base.php */