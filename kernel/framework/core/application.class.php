<?php
/*##################################################
 *                           application.class.php
 *                            -------------------
 *   begin                : August 17 2008
 *   copyright            : (C) 2008 Lo�c Rouchon
 *   email                : loic.rouchon@phpboost.com
 *
 *
###################################################
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
###################################################*/

define('APPLICATION_TYPE__KERNEL', 'kernel');
define('APPLICATION_TYPE__MODULE', 'module');
define('APPLICATION_TYPE__TEMPLATE', 'template');

class Application
{
    function Application($id, $language, $type = APPLICATION_TYPE__MODULE , $version = 0, $repository = '')
    {
        $this->id = $id;
        $this->language = $language;
        $this->type = $type;
        
        $this->repository = $repository;
        
        $this->version = $version;
    }
    
    function load(&$xml_desc)
    {
        //echo '<hr /><pre>'; print_r($xml_desc); echo '</pre>';
        $attributes = $xml_desc->attributes();
        
        $this->language = Application::_get_attribute($xml_desc, 'language');
        
        $this->version = Application::_get_attribute($xml_desc, 'num');
        $this->pubdate = Application::_get_attribute($xml_desc, 'pubdate');
        $this->security_update = Application::_get_attribute($xml_desc, 'security-update');
        $this->security_update = strtolower($this->security_update) == 'true' ? true : false;
        
        $this->priority = Application::_get_attribute($xml_desc, 'priority');
        switch( $this->priority )
        {
            case 'high':
                $this->priority = PRIORITY_HIGH;
                break;
            case 'medium':
                $this->priority = PRIORITY_MEDIUM;
                break;
            default:
                $this->priority = PRIORITY_LOW;
                break;
        }
        if( $this->security_update )
                $this->priority++;
        
        $this->download_url =  Application::_get_attribute($xml_desc, 'url', '//download');
        $this->update_url = Application::_get_attribute($xml_desc, 'url', '//update');;
        
        $this->authors = array();
        $authors_elts = $xml_desc->xpath('authors/author');
        foreach( $authors_elts as $author )
        {
            $this->authors[] = array('name' => Application::_get_attribute($author, 'name'), 'email' => Application::_get_attribute($author, 'email'));
        }
        
        $this->description = $xml_desc->xpath('description');
        $this->description = (string) $this->description[0];
        
        $this->new_features = array();
        $this->improvments = array();
        $this->bug_corrections = array();
        $this->security_improvments = array();
        
        $novelties = $xml_desc->xpath('whatsnew/new');
        foreach( $novelties  as $novelty  )
        {
            $attributes = $novelty->attributes();
            $type = isset($attributes['type']) ? $attributes['type'] : 'feature';
            switch( $type )
            {
                case 'improvment':
                    $this->improvments[] = (string) $novelty;
                    break;
                case 'bug':
                    $this->bug_corrections[] = (string) $novelty;
                    break;
                case 'security':
                    $this->security_improvments[] = (string) $novelty;
                    break;
                default:
                    $this->new_features[] = (string) $novelty;
                    break;
            }
        }
        
        $this->warning_level = Application::_get_attribute($xml_desc, 'level', 'warning');
        if( !empty($this->warning_level) )
        {
            $this->warning = $xml_desc->xpath('warning');
            $this->warning = (string) $this->warning[0];
        }
    }
    
    function identifier()
    {
        return md5($this->type . '_' . $this->id . '_' . $this->version . '_' . $this->language);
    }
    
    ## SERIALIZATION ##
    
    function serialize()
    {
        return serialize($this);
    }
    
    function unserialize(&$serialized_application)
    {
        $this->id = $serialized_application->id;
        $this->language = $serialized_application->language;
        $this->type = $serialized_application->type;
        
        $this->repository = $serialized_application->repository;
        
        $this->version = $serialized_application->version;
        $this->pubdate = $serialized_application->pubdate;
        $this->priority = $serialized_application->priority;
        $this->security_update = $serialized_application->security_update;
        
        $this->download_url = $serialized_application->download_url;
        $this->update_url = $serialized_application->update_url;
        
        $this->authors = $serialized_application->authors;
        
        $this->description = $serialized_application->description;
        $this->new_features = $serialized_application->new_features;
        $this->improvments = $serialized_application->improvments;
        $this->bug_corrections = $serialized_application->bug_corrections;
        $this->security_improvments = $serialized_application->security_improvments;
        
        $this->warning_level = $serialized_application->warning_level;
        $this->warning = $serialized_application->warning;
    }
    
    ## PUBLIC ACCESSORS ##
    
    function get_id() { return $this->id; }
    function get_language() { return $this->language; }
    function get_type() { return $this->type; }
    
    function get_repository() { return $this->repository; }
    
    function get_version() { return $this->version; }
    function get_pubdate() { return $this->pubdate; }
    function get_priority() { return $this->priority; }
    function get_security_update() { return $this->security_update; }
    
    function get_download_url() { return $this->download_url; }
    function get_update_url() { return $this->update_url; }
    
    function get_authors() { return $this->authors; }
    
    function get_description() { return $this->description; }
    function get_new_features() { return $this->new_features; }
    function get_improvments() { return $this->improvments; }
    function get_bug_corrections() { return $this->bug_corrections; }
    function get_security_improvments() { return $this->security_improvments; }
    
    function get_warning_level() { return $this->warning_level; }
    function get_warning() { return $this->warning; }
    
    ## PRIVATE METHODS ##
    
    function _get_attribute(&$xdoc, $attibute_name, $xpath_query = '.')
    {
        $elements = $xdoc->xpath($xpath_query);
        if( count($elements) > 0 )
        {
            $attributes = $elements[0]->attributes();
            return (string) (isset($attributes[$attibute_name]) ? $attributes[$attibute_name] : null);
        }
        return null;
    }
    
    
    ## PRIVATE ATTRIBUTES ##
    
    var $id = '';
    var $language = '';
    var $type = '';
    
    var $repository = '';
    
    var $version = '';
    var $pubdate = null;
    var $priority = null;
    var $security_update = false;
    
    var $download_url = '';
    var $update_url = '';
    
    var $authors = array();
    
    var $description = '';
    var $new_features = array();
    var $improvments = array();
    var $bug_corrections = array();
    var $security_improvments = array();
    
    var $warning_level = null;
    var $warning = null;
    
};

?>