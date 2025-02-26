<?php
namespace Magepattern\Component\HTTP;
use Magepattern\Component\Debug\Logger;

class JSON
{
    /**
     * Check if there was an Error with the last json request
     * @return bool
     */
    private function stack_error(): bool
    {
        try {
            $error = match(json_last_error()) {
                JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
                JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
                JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
                JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
                JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
                default => ''
            };
            if (!empty($error)) throw new \Exception('JSON Error: '.$error,E_WARNING);
            return true;
        }
        catch (\Exception $e) {
            Logger::getInstance()->log($e);
            return false;
        }
    }
}