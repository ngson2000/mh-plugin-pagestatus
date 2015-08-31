<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-pagestatus
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypePagestatus extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.pagestatus');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.pagestatus');
    }

    public static function get_categories() {
        return array('general' => 13000);
    }

    public static function get_viewtypes() {
        return array('portfolio');
    }

    public static function single_only() {
        return true;
    }

    public static function should_ajaxify() {
        return false;
    }

    public static function has_title_link() {
        return false;
    }

    public static function allowed_in_view(View $view) {
        // Only allowed in users' portfolio page
        return $view->get('type') == 'portfolio'
            && $view->get('group') == null
            && $view->get('institution') == null;
    }

    public static function get_artefacts(BlockInstance $instance) {
        return array();
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $statusoptions = self::get_allowed_status();
        $configdata = $instance->get('configdata');
        $smarty = smarty_core();
        if (isset($configdata['status'])
            && isset($statusoptions[$configdata['status']])) {
            $smarty->assign('status', $statusoptions[$configdata['status']]);
        }
        else {
            $smarty->assign('status', $statusoptions[0]);
        }
        return $smarty->fetch('blocktype:pagestatus:content.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $statusoptions = self::get_allowed_status();
        $configdata = $instance->get('configdata');

        $view = $instance->get_view();
        $status = 0;
        if (isset($configdata['status'])
            && isset($statusoptions[$configdata['status']])) {
            $status = $configdata['status'];
        }

        $elements = array (
            'status' => array (
                'type' => 'select',
                'options' => $statusoptions,
                'title' => get_string('pagestatus', 'blocktype.pagestatus'),
                'defaultvalue' => $status,
            ),
        );
        return $elements;
    }

    public static function instance_config_save($values, $instance) {
        return $values;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    public static function rewrite_blockinstance_config(View $view, $configdata) {
        // Reset the status when copying
        $configdata['status'] = 0;
        return $configdata;
    }

    /**
     *
     * @param array $biconfig   The block instance config
     * @param array $viewconfig The view config
     * @return BlockInstance    The newly made block instance
     */
    public static function import_create_blockinstance_leap(array $biconfig, array $viewconfig) {
        $statusoptions = self::get_allowed_status();
        $configdata = $biconfig['config'];
        // Make sure the import status description and ours are a match
        if ($key = array_search($configdata['statusdescription'], $statusoptions) !== false) {
            $configdata['status'] = $key;
        }
        else {
            $configdata['status'] = 0;
        }
        unset($configdata['statusdescription']);

        $bi = new BlockInstance(0,
            array(
                'blocktype'  => $biconfig['type'],
                'configdata' => $configdata,
            )
        );

        return $bi;
    }

    /**
     * Set the status property of the block config
     *
     * @param BlockInstance $bi The blockinstance to export the config for.
     * @return array The config for the blockinstance
     */
    public static function export_blockinstance_config_leap(BlockInstance $bi) {
        $statusoptions = self::get_allowed_status();
        $configdata = $bi->get('configdata');
        $result = array(
            'status' => json_encode(array($configdata['status'])),
            'statusdescription' => json_encode(array($statusoptions[$configdata['status']]))
        );
        return $result;
    }

    private static function get_allowed_status() {
        static $statusoptions = array();
        if (!empty($statusoptions)) {
            return $statusoptions;
        }
        else {
            return $statusoptions = array(
                0 => get_string('notset', 'blocktype.pagestatus'),
                1 => get_string('inprogress', 'blocktype.pagestatus'),
                2 => get_string('needhelp', 'blocktype.pagestatus'),
                3 => get_string('ready', 'blocktype.pagestatus'),
            );
        }
    }

}
