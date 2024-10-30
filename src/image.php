<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SRC;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

class Image
{
    public $type;
    public $thumbnailUrl;
    public $imageUrl;

    public function __construct($type, $id = 0)
    {
        $this->type = $type;
        if ($id > 0)
        {
            $this->thumbnailUrl = wp_get_attachment_image_src($id, 'thumbnail')[0];
            $this->imageUrl = wp_get_attachment_url($id);
        }
        else
        {
            $plugin = \SOSIDEE_MFA_PHOTOEDITOR\SosPlugin::instance();
            $this->thumbnailUrl = "{$plugin->dataUrl}/{$type}-thb.png";
            $this->imageUrl = "{$plugin->dataUrl}/{$type}.png";
        }
    }

}