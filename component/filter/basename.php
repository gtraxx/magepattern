<?php
/**
 * Created by Magix Dev.
 * User: aureliengerits
 * Date: 19/07/12
 * Time: 22:46
 *
 */
class filter_basename
{
    /**
     * Returns basename($value)
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        return basename((string) $value);
    }
}