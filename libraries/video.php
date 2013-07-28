<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * CodeIgniter Video Class
 *
 * Various functions on popular video hosting platforms
 *
 * @package        	CodeIgniter
 * @subpackage    	Libraries
 * @category    	Libraries
 * @author        	Nicolas Delhaume
 * @created		26/07/2013
 * @license             http://nicolasdelhaume.com
 * @link                n/a
 */
class Media {

    
    /**
     * @var  string  the provider of the video [youtube,videmo,dailymotion]
     */
    private $available_providers = array("youtube", "vimeo", "dailymotion");

    /**
     * @var  string  the provider of the video [youtube,videmo,dailymotion]
     */
    protected $provider = "youtube";

    /**
     * @var  string  the video file ID
     */
    protected $videoID;

    /**
     * @var  int  the default height of the video
     */
    protected $height;

    /**
     * @var  int  the default width of the video
     */
    protected $width;

    /**
     * @var  bool  define if the thumb is in HQ
     */
    protected $hqthumb = false;

    /**
     * @var  bool  define if the playback must start in auto mode
     */
    protected $autoplay = FALSE;

    /**
     * Overloads default class properties from the options.
     *
     * Any of the provider options can be set here, such as app_id or secret.
     *
     * @param   array $options provider options
     * @throws  Exception if a required option is not provided
     */
    function __construct() {

        $this->obj = & get_instance();
    }

    /**
     * Initialise the object with provided params.
     *
     * Any of the object options can be set here, such as provider or videoID.
     * Options also available are: height, width, autoplay
     *
     * @param   array $options Options for the object
     * @throws  Exception if a the provider is not in the available list
     */
    function init($options = array()) {

        if (empty($options['provider'])) {
            throw new Exception('Required option not provided: provider');
        }

        if (empty($options['videoID'])) {
            throw new Exception('Required option not provided: videoID');
        }

        if (!in_array($options['provider'], $this->available_providers)) {
            throw new Exception('The video provider you had set is not available');
        }
       
        isset($options['provider']) and $this->provider = $options['provider'];
        isset($options['videoID']) and $this->videoID = $options['videoID'];
        isset($options['width']) and $this->width = $options['width'];
        isset($options['height']) and $this->height = $options['height'];
        isset($options['autoplay']) and $this->autoplay = $options['autoplay'];
        isset($options['hqthumb']) and $this->autoplay = $options['hqthumb'];
    }

    /**
     * Returns the thumb image URL of the video
     *
     * @param   bool $hq define if the thumb must be in HQ
     * @throws  Exception if a the provider is not in the available list
     */
    function thumb() {

        $format = ($this->hqthumb) ? 'hqthumb' : 'thumb';

        $params = array($this->videoID, $format);


        $method = "get_" . $this->provider;

        if (!method_exists($this, $method)) {
            throw new Exception('The video provider you had set is not available');
        }

        return call_user_func_array(array($this, $method), $params);
    }

    /**
     * Returns the embed code of the video
     *
     * @throws  Exception if a the provider is not in the available list
     */
    function embed() {
        
        $params = array($this->videoID,
            'embed',
            $this->width,
            $this->height);

        $method = "get_" . $this->provider;

        if (!method_exists($this, $method)) {
            throw new Exception('The video provider you had set is not available');
        }

        return call_user_func_array(array($this, $method), $params);
    }

    /**
     * @package DailyMation Video parser 
     * @param $id > youtube id
     * @param $return >  default embed , thumb ,hqtgumb
     * @param $width > default 560
     * @param $height  > default 349
     * @param $rel > default cigenerate
     */
    private function get_dailymotion($id, $return = 'embed', $width = '', $height = '') {

        $site = file_get_contents("http://www.dailymotion.com/services/oembed?format=json&url=http://www.dailymotion.com/video/$id");

        $convert = json_decode($site);
        $thumbs = $convert->thumbnail_url;


        if ($return == 'embed') {
            return '<iframe src="http://www.dailymotion.com/embed/video/' . $id . '" width="' . ($width ? $width : 560) . '" height="' . ($height ? $height : 349) . '" frameborder="0"></iframe>';
        }
        else if ($return == 'thumb' || $return == 'hqthumb') {
            return $thumbs;
        }
        // else return id
        else {
            return $id;
        }
    }

    /**
     * @package Youtube Video parser 
     * @param $id > youtube id
     * @param $return >  default embed , thumb ,hqtgumb
     * @param $width > default 560
     * @param $height  > default 349
     * @param $rel > default cigenerate
     */
    private function get_youtube($id, $return = 'embed', $width = '', $height = '', $rel = "cigenerate") {
        //return embed iframe
        if ($return == 'embed') {
            $r = "<iframe src='http://www.youtube.com/embed/$id?rel=$rel' frameborder='0' width='" . ($width ? $width : 560) . "' height='" . ($height ? $height : 349) . "'></iframe>";
            return $r;
        }
        //return normal thumb
        else if ($return == 'thumb') {
            return 'http://i1.ytimg.com/vi/' . $id . '/default.jpg';
        }
        //return hqthumb
        else if ($return == 'hqthumb') {
            return 'http://i1.ytimg.com/vi/' . $id . '/hqdefault.jpg';
        }
        // else return id
        else {
            return $id;
        }
    }

    /**
     * @package Youtube Video parser 
     * @param $id > youtube id
     * @param $return >  default embed , thumb ,hqtgumb
     * @param $width > default 560
     * @param $height  > default 349
     */
    private function get_vimeo($id, $return = 'embed', $width = '', $height = '') {

        $site = file_get_contents("http://vimeo.com/api/v2/video/$id.json");
        $convert = json_decode($site);
        $thumbs = $convert[0]->thumbnail_large;

        if ($return == 'embed') {
            $e = '<iframe src="http://player.vimeo.com/video/' . $id . '" width="' . ($width ? $width : 560) . '" height="' . ($height ? $height : 349) . '" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
            return $e;
        }
        //return normal thumb
        else if ($return == 'thumb' || $return == 'hqthumb') {
            return $thumbs;
        }
        // else return id
        else {
            return $url;
        }
    }

}
