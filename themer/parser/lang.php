<?php
/**
 * Themer
 *
 * A Tumblr theme parser for local development.
 *
 * @package   Themer
 * @author    Braden Schaeffer <braden.schaeffer@gmail.com>
 * @version   0.1.0
 * @link      http://github.com/bschaeffer/themer
 *
 * @copyright Copyright (c) 2011
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 *
 * @filesource
 */

namespace Themer\Parser;

use Themer\Data;
use Themer\Parser\Variable;

require_once 'SymfonyComponents/YAML/sfYaml.php';

/**
 * Themer Language Class 
 *
 * Parses Tumblr's {lang:Item} variables
 *
 * @package     Themer
 * @subpackage  Parser
 * @author      Braden Schaeffer <braden.schaeffer@gmail.com>
 */
class Lang {
  
  private static $_lang = 'en';
  
  /**
   * Parses Tumblr's lang:Item tags
   * 
   * @static
   * @access  public
   * @param   string  the theme text
   * @return  string  the parsed theme text
   */
  public static function render($theme)
  {
    $lang = self::_load_lang();
    
    foreach($lang as $k => $v)
    {
      $tag = 'lang:'.$k;
      $theme = Variable::render($theme, $tag, $v, FALSE);
    }
    
    return $theme;
  }
  
  /**
   * Loads a language file
   * 
   * @static
   * @access  private
   * @return  array   the language data
   */
  private static function _load_lang()
  {
    $path = THEMER_BASEPATH.'themer/locales/'.static::$_lang.YML;
    $lang = \sfYaml::load($path);
    return $lang;
  }
}

/* End of file lang.php */
/* Location: ./themer/parser/lang.php */