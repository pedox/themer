<?php
/**
 * Themer
 *
 * A Tumblr theme parser for local development.
 *
 * @package   Themer
 * @author    Braden Schaeffer <braden.schaeffer@gmail.com>
 * @version   beta
 * @link      http://github.com/bschaeffer/themer
 *
 * @copyright Copyright (c) 2011
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 *
 * @filesource
 */
namespace Themer\Parser;

use Themer\Data;
use Themer\Parser;

/**
 * Themer Paginate Class 
 *
 * Renders pagination data and template tags
 *
 * @package     Themer
 * @subpackage  Parser
 * @author      Braden Schaeffer
 */
class Paginate {
  
  const BLOCK_INDEX = 'Pagination';
  const BLOCK_PERMALINK = 'PermalinkPagination';

  public static $page_number  = 1;
  public static $per_page     = 6;
  
  protected static $_index_pages = array('Index', 'Tag', 'Search', 'Day');
  
  /**
   * Renders pagination data into the theme
   *
   * @access  public
   * @param   string  the theme contents to parse
   * @return  string  the parsed theme contents
   */
  public static function render($theme)
  { 
    $current_page_name = Parser\Pages::$page;
    
    if(in_array($current_page_name, static::$_index_pages))
    {
      $theme = self::index($theme);
    }
    elseif($current_page_name === 'Permalink')
    {
      $theme = self::permalink($theme);
    }
    
    return $theme;
  }
  
  /**
   * Renders pagination data for index pages
   *
   * @access  public
   * @param   string  the theme contents to parse
   * @return  string  the parsed theme contents
   */
  public static function index($theme)
  {
    $theme = Block::remove($theme, self::BLOCK_PERMALINK);
    
    if(count(Parser::$post_data) <= static::$per_page)
    {
      $theme = Block::remove($theme, self::BLOCK_INDEX);
      return $theme;
    }
  
    $data = static::_parse_pages(static::$page_number, static::$per_page);
    
    return self::_render_pagination($theme, self::BLOCK_INDEX, $data);
  }
  
  /**
   * Renders pagination data for permalink pages
   *
   * @access  public
   * @param   string  the theme contents to parse
   * @return  string  the parsed theme contents
   */
  public static function permalink($theme)
  {
    $theme = Block::remove($theme, self::BLOCK_INDEX);
    
    if(count(Parser::$post_data) === 1)
    {
      $theme = Block::remove($theme, self::BLOCK_PERMALINK);
      return $theme;
    }
    
    $url = '/post/'.static::$post_data[0]['PostID'];
    $data['NextPost']      = $url;
    $data['PreviousPost']  = $url;
    
    return self::_render_pagination($theme, self::BLOCK_PERMALINK, $data);
  }
  
  private static function _render_pagination($theme, $block_name, $data)
  {
    foreach(Block::find($theme, $block_name) as $block)
    {
      $tmp = Block::render($block, $block_name);
      
      foreach($data as $k => $v)
      {
        // If the value is empty, just remove the block
        if(empty($v))
        {
          $tmp = Block::remove($tmp, $k);
        }
        else
        {
          $tmp = Block::render($tmp, $k);
          $tmp = Variable::render($tmp, $k, $v);
        }
      }
      
      $theme = str_replace($block, $tmp, $theme);
    }
    
    return $theme;
  }
  
  private static function _parse_pages($current_page = 1, $per_page = 6)
  {
    $all_posts = Data::get('posts');
    
    $total = count($all_posts);
    $total_pages = ceil($total / $per_page);
    
    // If we are asking for a page number that's not found...
    // the page is not found :)
    
    if($current_page > $total_pages)
    {
      Parser::not_found();
    }
    
    // We need to figure out where to start the post offset...
    $start = ($current_page == 1) ? 0 : ($current_page - 1) * $per_page;
    
    // then we clip from the beginning of the post data...
    $clipped = array_slice(Parser::$post_data, $start, count($all_posts));
    
    // then we clip the remaining post so we can have a 'page'...
    $final = array_slice($clipped, 0, $per_page);
  
    // and finally we set the new, paginated post data as the data to be parsed
    Parser::$post_data = $final;
  
    $next_page = '';
    $previous_page = '';
  
    if($current_page != 1)
    {
      $previous = $current_page - 1;
      $previous_page = '/page/'.$previous;
    }
    
    if($current_page != $total_pages)
    {
      $next = $current_page + 1;
      $next_page = '/page/'.$next;
    }
  
    
    $data = array(
      'TotalPages'    => $total_pages,
      'CurrentPage'   => $current_page,
      'NextPage'      => $next_page,
      'PreviousPage'  => $previous_page
    );
    
    return $data;
  }
}

/* End of file paginate.php */
/* Location: ./themer/parser/paginate.php */