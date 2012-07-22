<?php
/**
 * Created by Magix Dev.
 * User: aureliengerits
 * Date: 22/07/12
 * Time: 01:47
 *
 */
class form_input{
    /**
     *
     * @param unknown_type $nid
     * @param unknown_type $name
     * @param unknown_type $id
     */
    private static function getNameAndId($nid,&$name,&$id)
    {
        if (is_array($nid)) {
            $name = $nid[0];
            $id = !empty($nid[1]) ? $nid[1] : null;
        } else {
            $name = $id = $nid;
        }
    }

    /**
     * Input field
     *
     * Returns HTML code for an input field. $nid could be a string or an array of
     * name and ID.
     *
     * @param string|array    $nid            Element ID and name
     * @param integer        $size        Element size
     * @param integer        $max            Element maxlength
     * @param string        $default        Element value
     * @param bool|string $class Element class name
     * @param string        $tabindex        Element tabindex
     * @param boolean        $disabled        True if disabled
     *
     * @param bool $readonly
     * @return string
     */
    public static function field($nid, $size, $max, $default='',$class=true, $tabindex='',$disabled=false,$readonly=false)
    {
        self::getNameAndId($nid,$name,$id);

        $res = '<input type="text" size="'.$size.'" name="'.$name.'" ';

        $res .= $id ? 'id="'.$id.'" ' : '';
        $res .= $max ? 'maxlength="'.$max.'" ' : '';
        $res .= $default || $default === '0' ? 'value="'.$default.'" ' : '';
        $res .= $class ? 'class="'.$class.'" ' : '';
        $res .= $tabindex ? 'tabindex="'.$tabindex.'" ' : '';
        $res .= $disabled ? 'disabled="disabled" ' : '';
        $res .= $readonly ? 'readonly="readonly" ' : '';
        $res .= ' />';
        return $res;
    }

    /**
     * Textarea
     *
     * Returns HTML code for a textarea. $nid could be a string or an array of
     * name and ID.
     *
     * @param string|array    $nid            Element ID and name
     * @param integer        $cols        Number of columns
     * @param integer        $rows        Number of rows
     * @param string        $default        Element value
     * @param bool|string $class Element class name
     * @param string        $tabindex        Element tabindex
     * @param boolean        $disabled        True if disabled
     * @internal param string $extra_html Extra HTML attributes
     *
     * @return string
     */
    public static function textArea($nid, $cols=20, $rows=30, $default='',$class='',$tabindex='', $disabled=false)
    {
        self::getNameAndId($nid,$name,$id);

        $res = '<textarea cols="'.$cols.'" rows="'.$rows.'" ';
        $res .= 'name="'.$name.'" ';
        $res .= $id ? 'id="'.$id.'" ' : '';
        $res .= ($tabindex != '') ? 'tabindex="'.$tabindex.'" ' : '';
        $res .= $class ? 'class="'.$class.'" ' : '';
        $res .= $disabled ? 'disabled="disabled" ' : '';
        $res .= '>';
        $res .= $default;
        $res .= '</textarea>';

        return $res;
    }
}
?>