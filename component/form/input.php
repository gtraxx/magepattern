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
     * @example :
         $form = new form_input();
         print $form->field('myfield',30,30,'','myclass');
         return <input type="text" size="30" name="myfield" id="myfield" maxlength="30" class="myclass"  />
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
        $res .= '</textarea>';

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

        $res .= ' />';

        return $res;
    }
}
?>