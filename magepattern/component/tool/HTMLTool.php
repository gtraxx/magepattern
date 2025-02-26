<?php
namespace Magepattern\Component\Tool;

class HTMLTool
{
    /**
     * HTML escape
     * Replaces HTML special characters by entities.
     *
     * @param string $str String to escape
     * @return string
     */
    public static function escapeHTML(string $str): string
    {
        return htmlspecialchars($str,ENT_COMPAT,'UTF-8');
    }

    /**
     * HTML Extreme escape
     * Replaces HTML characters by entities.
     *
     * @param string $str String to escape
     * @return string
     */
    public static function escapeExtremeHTML(string $str): string
    {
        return htmlentities($str,ENT_COMPAT,'UTF-8');
    }

    /**
     * decode Extreme htmlentities
     *
     * @param string $str
     * @return string
     */
    public static function decodeExtremeHTML(string $str): string
    {
        return html_entity_decode($str,ENT_COMPAT,'UTF-8');
    }

    /**
     * Decode HTML entities
     * Returns a string with all entities decoded.
     *
     * @param string string $str  String to protect
     * @param bool $keep_special Keep special characters: &gt; &lt; &amp;
     * @return string
     */
    public static function decodeEntities(string $str, $keep_special = false): string
    {
        if ($keep_special) $str = str_replace(['&amp;','&gt;','&lt;'], ['&amp;amp;','&amp;gt;','&amp;lt;'], $str);

        # Some extra replacements
        $extra = ['&apos;' => "'"];

        $str = str_replace(array_keys($extra),array_values($extra),$str);

        return html_entity_decode($str,ENT_QUOTES,'UTF-8');
    }

    /**
     * function encode entities HTML
     *
     * @param string $str
     * @param bool $keep_special
     * @return string
     */
    public static function encodeEntities(string $str,$keep_special = false): string
    {
        if ($keep_special) $str = str_replace(['&','<','</','>'], ['&amp;', '&lt;','&lt;/','&gt;'], $str);

        # Some extra replacements
        $extra = ["'" => '&apos;'];

        return str_replace(array_keys($extra),array_values($extra),$str);
    }

    /**
     * URL escape
     * Returns an escaped URL string for HTML content
     *
     * @param string $str String to escape
     * @return string
     */
    public static function escapeURL(string $str): string
    {
        return str_replace('&','&amp;',$str);
    }

    /**
     * Javascript escape
     * Returns a protected JavaScript string
     *
     * @param string $str String to protect
     * @return string
     */
    public static function escapeJS(string $str): string
    {
        $str = htmlspecialchars($str,ENT_NOQUOTES,'UTF-8');
        $str = str_replace("'","\"",$str);
        return str_replace('"','\"',$str);
    }
}