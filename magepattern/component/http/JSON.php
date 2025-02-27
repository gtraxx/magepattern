<?php
namespace Magepattern\Component\HTTP;
use Magepattern\Component\Debug\Logger;

class JSON
{
    /**
     * Default options for JSON operations.
     *
     * @var array
     */
    private static array $defaultOptions = [
        'decode' => [
            'assoc' => false,
            'depth' => 512,
            'options' => 0,
        ],
        'encode' => [
            'options' => 0,
            'depth' => 512,
        ],
    ];

    /**
     * Decode a JSON string and check for errors.
     *
     * $jsonString = '{"name":"Jane Doe","age":25,"city":"London"}';
     *
     * $decodedData = $jsonInstance->decode($jsonString);
     *
     * if (is_array($decodedData) || is_object($decodedData)) {
     *      echo "Decoded data:\n";
     *      print_r($decodedData);
     * } else {
     *      echo "Error decoding JSON:\n";
     *      echo $decodedData; // $decodedData contient le message d'erreur
     * }
     *
     * // Exemple avec options personnalisées
     * $options = ['assoc' => true];
     * $assocArray = $jsonInstance->decode($jsonString, $options);
     *
     * if (is_array($assocArray)) {
     *      echo "\n\nDecoded data as associative array:\n";
     *      print_r($assocArray);
     * } else {
     *      echo "Error decoding JSON:\n";
     *      echo $assocArray; // $assocArray contient le message d'erreur
     * }
     *
     * @param string $json The JSON string to decode.
     * @param array|null $options Override default options.
     *
     * @return mixed
     */
    public function decode(string $json, ?array $options = null): mixed
    {
        $options = $options ? array_merge(self::$defaultOptions['decode'], $options) : self::$defaultOptions['decode'];
        $decoded = json_decode($json, $options['assoc'], $options['depth'], $options['options']);
        $error = $this->getLastError();
        if ($error) {
            Logger::getInstance()->log("JSON Decode Error: " . $error, 'php', 'error', Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return $error;
        }
        return $decoded;
    }

    /**
     * Encode a value to JSON and check for errors.
     *
     * use Magepattern\Component\HTTP\JSON;
     * $jsonInstance = new JSON();
     *
     * $data = [
     *      'name' => 'John Doe',
     *      'age' => 30,
     *      'city' => 'New York',
     * ];
     *
     * $json = $jsonInstance->encode($data);
     *
     * if (is_string($json)) {
     *      echo "JSON encoded data:\n";
     *      echo $json;
     * } else {
     *      echo "Error encoding JSON:\n";
     *      echo $json; // $json contient le message d'erreur
     * }
     *
     * // Exemple avec options personnalisées
     * $options = ['options' => JSON_PRETTY_PRINT];
     * $prettyJson = $jsonInstance->encode($data, $options);
     *
     * if (is_string($prettyJson)) {
     *      echo "\n\nPretty JSON encoded data:\n";
     *      echo $prettyJson;
     * } else {
     *      echo "Error encoding JSON:\n";
     *      echo $prettyJson; // $prettyJson contient le message d'erreur
     * }
     *
     * @param mixed $value The value to encode.
     * @param array|null $options Override default options.
     *
     * @return string
     */
    public function encode(mixed $value, ?array $options = null): string
    {
        $options = $options ? array_merge(self::$defaultOptions['encode'], $options) : self::$defaultOptions['encode'];
        $encoded = json_encode($value, $options['options'], $options['depth']);
        $error = $this->getLastError();
        if ($error) {
            Logger::getInstance()->log("JSON Encode Error: " . $error, 'php', 'error', Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return $error;
        }
        return $encoded;
    }

    /**
     * Get the last JSON error message.
     *
     * @return string|null The error message or null if no error occurred.
     */
    private function getLastError(): ?string
    {
        return match (json_last_error()) {
            JSON_ERROR_NONE => null,
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
            default => 'Unknown JSON error'
        };
    }
}