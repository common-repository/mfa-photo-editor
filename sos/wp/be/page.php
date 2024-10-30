<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\BE;
use \SOSIDEE_MFA_PHOTOEDITOR\SOS\WP as SOS_WP_ROOT;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

/**
 * Class for the admin console pages
 * 
 * @property string $name : the text displayed in the browser title and in the nav-tab
 * @property string $role : the capability required to be displayed to the user when added to a menu item
 * @property string $title : the text displayed in the title tags of the page when the menu item is selected
 * @property string $key
 * @property string $hook : the hook_suffix returned by add_menu_page(), add_submenu_page(), add_options_page(), etc.
 * @property string $menuType
 * @property string $menuColor
 */
class Page
{
    use SOS_WP_ROOT\Property
	{
        SOS_WP_ROOT\Property::__set as __setProp;
	}
    use SOS_WP_ROOT\Translation;

    private static $screen = null;

    public function __construct($path, $name)
    {
        $this->_addProperty('key', '');  
        $this->_addProperty('path', '');
        $this->_addProperty('url', '');
        $this->_addProperty('name', '');
        $this->_addProperty('role', 'manage_options');
        $this->_addProperty('title', '');
        $this->_addProperty('menuType', MenuType::CUSTOM);
        $this->_addProperty('menuColor', false);
        $this->_addProperty('hook', false);

        $this->path = $path;

        if ($name == '')
        {
            $name = sosidee_str_remove('.php', basename( $path) );
        }
        $this->name = $name;

    }

    public function __set($name, $value)
    {
        $ret = null;
        switch($name)
        {
            case 'path':
                if ( !sosidee_str_ends_with($value, '.php') )
                {
                    $value .= '.php';
                }
                $ret = $this->__setProp($name, $value);
                break;
            default:
                $ret = $this->__setProp($name, $value);
        }
        return $ret;
    }
    
    public function translate()
    {
        $this->name = self::t_( $this->name );
        $this->title = self::t_( $this->title );
    }
    
    public function isCurrent()
    {
        if ( is_null(self::$screen) ) {
            self::$screen = get_current_screen();
        }
        return sosidee_str_ends_with(self::$screen->id, $this->key );
    }
    
}