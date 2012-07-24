<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Mage Pattern.
# The toolkit PHP for developer, integrated in SC BOX
# Copyright (C) 2012  Gerits Aurelien <aurelien@magix-dev.be> - <aurelien@sc-box.com>
#
# OFFICIAL TEAM MAGE PATTERN:
#
#   * Gerits Aurelien (Author - Developer) <aurelien@sc-box.com>
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Redistributions of source code must retain the above copyright notice,
# this list of conditions and the following disclaimer.
#
# Redistributions in binary form must reproduce the above copyright notice,
# this list of conditions and the following disclaimer in the documentation
# and/or other materials provided with the distribution.
#
# DISCLAIMER

# Do not edit or add to this file if you wish to upgrade Mage Pattern to newer
# versions in the future. If you wish to customize Mage Pattern for your
# needs please refer to http://www.sc-box.com for more information.
#
# -- END LICENSE BLOCK -----------------------------------

class form_input{
    private static $getInput;
    /**
     * @var array
     */
    protected static $arrInputConfig = array(
        'class'     =>  '',
        'default'   =>  '',
        'tabindex'  =>  '',
        'disabled'  =>  false,
        'readonly'  =>  false,
        'size'      =>  '',
        'max'       =>  ''
    );
    private function setInputConfig($arrInputConfig = false){
        if($arrInputConfig){
            if(is_array($arrInputConfig)){
                $setInput = $arrInputConfig;
            }else{
                $setInput = self::$arrInputConfig;
            }
        }else{
            $setInput = self::$arrInputConfig;
        }

        if(is_array($setInput)){
            if(array_key_exists('class', $setInput)){
                self::$getInput['class'] = $setInput['class'];
            }else{
                self::$getInput['default'] = '';
            }
            if(array_key_exists('default', $setInput)){
                self::$getInput['default'] = $setInput['default'];
            }else{
                self::$getInput['default'] = '';
            }
            if(array_key_exists('tabindex', $setInput)){
                self::$getInput['tabindex'] = $setInput['tabindex'];
            }else{
                self::$getInput['tabindex'] = '';
            }
            if(array_key_exists('disabled', $setInput)){
                self::$getInput['disabled'] = $setInput['disabled'];
            }else{
                self::$getInput['disabled'] = false;
            }
            if(array_key_exists('readonly', $setInput)){
                self::$getInput['readonly'] = $setInput['readonly'];
            }else{
                self::$getInput['readonly'] = false;
            }
            if(array_key_exists('size', $setInput)){
                self::$getInput['size'] = $setInput['size'];
            }else{
                self::$getInput['size'] = '';
            }
            if(array_key_exists('max', $setInput)){
                self::$getInput['max'] = $setInput['max'];
            }else{
                self::$getInput['max'] = '';
            }
            return self::$getInput;
        }
    }
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
     * Select Field
     *
     * Return HTML CODE for SELECT MENU
     * @static
     * @param string|array    $nid            Element ID and name
     * @param array $arrayOption
     * @param string $class
     * @param string $default
     * @throws Exception
     * @internal param null $cvalue
     * @return string
     *
     * @example :
     * #### BASE #####
     *
     $form->select(
         'myselect',
         array(1=>'opt1',2=>'opt2'),
         'maclass'
     );
     * ##### WITH Database #######
        $fetch = $db->fetchAll($sql); //ASSOCIATIVE DATA
        $option = '';
        foreach($fetch as $value){
            $id[] = $value['id'];
            $color[] = $value['color'];
        }
        $selectcolor = array_combine($id,$color);
        $form->select(
         'monselect',
         $selectcolor,
         'maclass'
        );
     *
     * Return Source :
        <select name="myselect" id="myselect" class="myclass">
        <option value="1">couleur verte</option>
        <option value="2">couleur rouge</option>
       </select>
     *
     */
    public static function select($nid, $arrayOption,$arrInput=false){

        self::getNameAndId($nid,$name,$id);
        $getInput = self::setInputConfig($arrInput);
        if(is_array($arrayOption)){
            $res = '<select name="'.$name.'" ';
            $res .= $id ? 'id="'.$id.'"' : '';
            $res .= $getInput['class'] ? ' class="'.$getInput['class'].'"' : '';
            $res .= '>'."\n";
            foreach ($arrayOption as $key => $value){
                $selected = null;
                if(isset($getInput['default']) AND $getInput['default'] != ''){
                    if(array_key_exists($getInput['default'], $arrayOption)){
                        $selected = ($getInput['default'] == $key) ? ' selected="selected"': null;
                    }
                }
                $res .= '<option'.$selected.' value="'.$key.'">';
                $res .= $value;
                $res .= '</option>'."\n";
            }
            $res .= '</select>'."\n";

            return $res;

        }else{
            throw new Exception(sprintf('%s is not array in '.__METHOD__, $arrayOption));
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
     * @example :
         $form = new form_input();
         print $form->field('myfield',30,30,'','myclass');
         return <input type="text" size="30" name="myfield" id="myfield" maxlength="30" class="myclass"  />
     */
    public static function field($nid, $arrInput=false)
    {
        self::getNameAndId($nid,$name,$id);
        $getInput = self::setInputConfig($arrInput);
        $res = '<input type="text" size="'.$getInput['size'].'" name="'.$name.'" ';

        $res .= $id ? 'id="'.$id.'" ' : '';
        $res .= $getInput['max'] ? 'maxlength="'.$getInput['max'].'" ' : '';
        $res .= $getInput['default'] || $getInput['default'] === '0' ? 'value="'.$getInput['default'].'" ' : '';
        $res .= $getInput['class'] ? 'class="'.$getInput['class'].'" ' : '';
        $res .= $getInput['tabindex'] ? 'tabindex="'.$getInput['tabindex'].'" ' : '';
        $res .= $getInput['disabled'] ? 'disabled="disabled" ' : '';
        $res .= $getInput['readonly'] ? 'readonly="readonly" ' : '';
        $res .= ' />'."\n";
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
     * @example :
        $form = new form_input();
        print print $form->textArea('myfield',20,30,'Default text','myclass');
        return <textarea cols="20" rows="30" name="myfield" id="myfield" class="myclass" >Default text</textarea>
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
        $res .= '</textarea>'."\n";

        return $res;
    }

    /**
     * Password field
     *
     * Returns HTML code for a password field. $nid could be a string or an array of
     * name and ID.
     *
     * @param string|array	$nid			Element ID and name
     * @param integer		$size		Element size
     * @param integer		$max			Element maxlength
     * @param string		$default		Element value
     * @param string		$class		Element class name
     * @param string		$tabindex		Element tabindex
     * @param boolean		$disabled		True if disabled
     * @param string		$extra_html	Extra HTML attributes
     *
     * @return string
     */
    public static function password($nid, $size, $max, $default='', $class='', $tabindex='', $disabled=false, $extra_html='')
    {
        self::getNameAndId($nid,$name,$id);

        $res = '<input type="password" size="'.$size.'" name="'.$name.'" ';

        $res .= $id ? 'id="'.$id.'" ' : '';
        $res .= $max ? 'maxlength="'.$max.'" ' : '';
        $res .= $default || $default === '0' ? 'value="'.$default.'" ' : '';
        $res .= $class ? 'class="'.$class.'" ' : '';
        $res .= $tabindex ? 'tabindex="'.$tabindex.'" ' : '';
        $res .= $disabled ? 'disabled="disabled" ' : '';
        $res .= $extra_html;

        $res .= ' />'."\n";

        return $res;
    }

    /**
     * Radio button
     *
     * Returns HTML code for a radio button. $nid could be a string or an array of
     * name and ID.
     *
     * @param string|array    $nid            Element ID and name
     * @param string        $value        Element value
     * @param bool|string $checked True if checked
     * @param string        $class        Element class name
     * @param string        $tabindex        Element tabindex
     * @param boolean        $disabled        True if disabled
     * @param string        $extra_html    Extra HTML attributes
     *
     * @return string
     */
    public static function radio($nid, $value, $arrInput=false, $checked=false, $extra_html='')
    {
        self::getNameAndId($nid,$name,$id);
        $getInput = self::setInputConfig($arrInput);
        $res = '<input type="radio" name="'.$name.'" value="'.$value.'" ';

        $res .= $id ? 'id="'.$id.'" ' : '';
        $res .= $checked ? 'checked="checked" ' : '';
        $res .= $getInput['class'] ? 'class="'.$getInput['class'].'" ' : '';
        $res .= $getInput['tabindex'] ? 'tabindex="'.$getInput['tabindex'].'" ' : '';
        $res .= $getInput['disabled'] ? 'disabled="disabled" ' : '';
        $res .= $extra_html;

        $res .= '/>'."\n";

        return $res;
    }

    /**
     * Checkbox
     *
     * Returns HTML code for a checkbox. $nid could be a string or an array of
     * name and ID.
     *
     * @param string|array    $nid            Element ID and name
     * @param string        $value        Element value
     * @param bool|string $checked True if checked
     * @param string        $class        Element class name
     * @param string        $tabindex        Element tabindex
     * @param boolean        $disabled        True if disabled
     * @param string        $extra_html    Extra HTML attributes
     *
     * @return string
     */
    public static function checkbox($nid, $value, $checked='', $class='', $tabindex='',$disabled=false, $extra_html='')
    {
        self::getNameAndId($nid,$name,$id);

        $res = '<input type="checkbox" name="'.$name.'" value="'.$value.'" ';

        $res .= $id ? 'id="'.$id.'" ' : '';
        $res .= $checked ? 'checked="checked" ' : '';
        $res .= $class ? 'class="'.$class.'" ' : '';
        $res .= $tabindex ? 'tabindex="'.$tabindex.'" ' : '';
        $res .= $disabled ? 'disabled="disabled" ' : '';
        $res .= $extra_html;

        $res .= ' />'."\n";

        return $res;
    }
    /**
     * Hidden field
     *
     * Returns HTML code for an hidden field. $nid could be a string or an array of
     * name and ID.
     *
     * @param string|array	$nid			Element ID and name
     * @param string		$value		Element value
     *
     * @return string
     */
    public static function hidden($nid,$value)
    {
        self::getNameAndId($nid,$name,$id);

        $res = '<input type="hidden" name="'.$name.'" value="'.$value.'" ';
        $res .= $id ? 'id="'.$id.'"' : '';
        $res .= ' />'."\n";

        return $res;
    }
}
?>