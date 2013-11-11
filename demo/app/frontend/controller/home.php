<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Mage Pattern.
# The toolkit PHP for developer
# Copyright (C) 2012 - 2013 Gerits Aurelien contact[at]aurelien-gerits[dot]be
#
# OFFICIAL TEAM MAGE PATTERN:
#
#   * Gerits Aurelien (Author - Developer) contact[at]aurelien-gerits[dot]be
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
# needs please refer to http://www.magepattern.com for more information.
#
# -- END LICENSE BLOCK -----------------------------------
/**
 * Created by SC BOX.
 * User: aureliengerits
 * Date: 11/11/13
 * Time: 17:34
 * 
 */
class frontend_controller_home{
    /**
     * @var db_layer
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct(){
        $this->db = new db_layer();
    }
    /**
     * @return array
     */
    private function getCollection(){
        $sql =  'SELECT id, color
        FROM fruit';
        $fetch = $this->db->fetchAll($sql);
        return collections_ArrayTools::iteratorToArray(
            $fetch
        );
    }

    /**
     * @access private
     * get Result collection
     */
    private function getBuildCollection(){
        // Load collection
        $collection = $this->getCollection();
        // Collection in firebug
        foreach($collection as $key){
            $id[] = $key['id'];
            $color[] = $key['color'];
        }
        // Array combine for SQL request
        $collectionColor = array_combine($id,$color);
        // Init debug Firebug
        $firephp = new debug_firephp();
        $firephp->log($collectionColor);
        // Collection in print
        foreach($collection as $key => $value){
            print 'ID : ' .$value['id'].'&nbsp;,';
            print 'Color : ' .$value['color'].'<br />';
        }
    }

    /**
     * Return Date Format
     */
    private function getDateFormat(){
        $date = new date_dateformat();
        $datestart = $date->dateDefine('Y-m-d','now');
        $interval = $date->setInterval('2013-01-01','D');
        $dateend = $date->add(
            array('interval'=>$interval,'type'=>'object'),
            'Y-m-d',
            '2013-10-01'
        );
        print ucfirst($date->getStateDiff($dateend,$datestart)).' Date: '.$datestart;
    }

    /**
     * Display json data
     * @return array
     */
    private function getJsonData(){
        // SQL request
        $sql =  'SELECT id, color
        FROM fruit';
        $fetch = $this->db->fetchAll($sql);
        $json = new http_json();
        // Exemple with array Replace
        $result = $json->arrayJsonReplace(
            $fetch,
            array(  0 =>
                array(
                    'id' =>  0,
                    'color'=>'super truc'
                )
            )
        );
        return $result;
    }
    /**
     * Execute script
     */
    public function run(){
        if(http_request::isGet('json')){
            $json = new http_json();
            $header = new http_header();
            $header->head_expires("Mon, 26 Jul 1997 05:00:00 GMT");
            $header->head_last_modified(gmdate( "D, d M Y H:i:s" ) . "GMT");
            $header->pragma();
            $header->cache_control("nocache");
            $header->getStatus('200');
            $header->html_header("UTF-8");
            print $json->encode(
                $this->getJsonData(),array('','')
            );
        }else{
            $this->getBuildCollection();
            $this->getDateFormat();
        }
    }
}