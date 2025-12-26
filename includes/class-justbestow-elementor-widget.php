<?php
if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Justbestow_Elementor_Widget extends Widget_Base
{

    public function get_name()
    {
        return 'justbestow_widget';
    }

    public function get_title()
    {
        return __('Just Bestow', 'just-bestow');
    }

    public function get_icon()
    {
        return 'eicon-form-horizontal';
    }

    public function get_categories()
    {
        return ['general'];
    }

    public function register_controls()
    {
        /* add control settings if required for now we don't */
    }

    protected function render()
    {
        /* utilize the existing render function */
        echo justbestow_render_block();
    }

    protected function _content_template()
    {
?>
        <div class="t2pw-widget-placeholder" style="padding: 16px; border: 1px dashed #ccc; border-radius: 6px; text-align: center;">
            <?php echo esc_html__('Just Bestow will appear on frontend.', 'just-bestow'); ?>
        </div>
<?php
    }
}
