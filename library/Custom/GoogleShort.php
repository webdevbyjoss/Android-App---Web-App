<?php
/**
 * Googl
 *
 * For more information on this file and how to use the class please visit
 * http://www.hashbangcode.com/blog/googl-url-shortening-service-class-php-528.html
 *
 * PHP version 5
 *
 * @category Google URL Shortener API
 * @package  Google URL Shortener API
 * @author   Philip Norton <philipnorton42@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.hashbangcode.com/
 *
 */

/**
 * This class allows interaction with the Goo.gl URL shortening service.
 *
 * @category Google URL Shortener API
 * @package  Google URL Shortener API
 * @author   Philip Norton <philipnorton42@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.hashbangcode.com/blog/googl-url-shortening-service-class-php-528.html
 *
 */
class Custom_GoogleShort {

    /**
     * The API key used for the API connection.
     *
     * @var string
     */
    private $apikey;

    /**
     * The API call is encoded into the URL.
     *
     * @var string
     */
    private $version = 'v1';

    /**
     * The raw data returned from goo.gl
     *
     * @var array
     */
    private $results;

    /**
     * The any error messages returned from goo.gl
     *
     * @var mixed
     */
    private $errors = false;

    /**
     *
     *
     * @var mixed
     */
    private $googlUrl;

    /**
     * Constructor
     *
     * @param string $apiKey The API key to use for the connection.
     */
    public function __construct($apiKey = null) {
        $this->apiKey = $apiKey;
        $this->googlUrl = "https://www.googleapis.com/urlshortener/" . $this->version . "/url";
    }

    /**
     * Get the latest errors.
     *
     * @return array An array containing the latest errors.
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get the latest results from the goo.gl service. If no errors are set
     * the function will return false.
     *
     * @return array An array containing the goo.gl results.
     */
    public function getResults() {
        return $this->results;
    }

    /**
     * Shorten a URL using the goo.gl service.
     *
     * @param string $url The URL to shorten.
     *
     * @return mixed The shortened URL or false if the call failed.
     */
    public function shorten($url) {
        if (!$this->isValidUrl($url)) {
            throw new NonValidUrlException('Not a valid URL.');
        }

        $parameters = '{"longUrl": "' . $url . '"}';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, true);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));

        $url = $this->googlUrl;

        if (!is_null($this->apiKey)) {
            $url .= '?key=' . $this->apiKey;
        }

        curl_setopt($curl, CURLOPT_URL, $url);

        if ($this->sendRequest($curl)) {
            return $this->results->id;
        }

        return false;
    }

    /**
     * Get some analytics information about a short URL.
     *
     * @param string $shortUrl The short URL.
     *
     * @return mixed The analytics information, or false if the call failed.
     */
    public function analytics($shortUrl) {
        if (!$this->isValidUrl($shortUrl)) {
            throw new NonValidUrlException('Not a valid URL.');
        }

        $url = $this->googlUrl . '?shortUrl=' . $shortUrl;

        $url .= '&projection=FULL';

        if (!is_null($this->apiKey)) {
            $url .= '&key=' . $this->apiKey;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);

        if ($this->sendRequest($curl)) {
            return $this->results;
        }

        return false;
    }

    /**
     * Expand a URL that has been shortened using the goo.gl service.
     *
     * @param string $url The URL to expand.
     *
     * @return string The long URL, as translated by the goo.gl service, or
     *                false if the call failed.
     */
    public function expand($shortUrl) {
        if (!$this->isValidUrl($shortUrl)) {
            throw new NonValidUrlException('Not a valid URL.');
        }

        $url = $this->googlUrl . '?shortUrl=' . $shortUrl;

        if (!is_null($this->apiKey)) {
            $url .= '&key=' . $this->apiKey;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);

        if ($this->sendRequest($curl)) {
            return $this->results->longUrl;
        }

        return false;
    }

    /**
     * Get the results from a goo.gl service interaction.
     *
     * @param resource $curl The curl resource to use for this transaction.
     *
     * @return boolean True if everything has worked, otherwise false. The results
     *                 will be stored in the $results variable, accessable via the
     *                 getRawResults() method. Any errors will be stored in the
     *                 $errors variable, accessable via the getErrors() method.
     */
    protected function sendRequest($curl) {
        if (!is_resource($curl)) {
            throw new NotAResourceException('$curl is not a resource.');
        }

        curl_setopt($curl, CURLOPT_SSLVERSION, 3);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($curl);

        if ($data === false) {
            throw new CurlErrorException(curl_error($curl));
        }

        curl_close($curl);

        $this->results = json_decode($data);

        // Reset errors.
        $this->errors = false;

        if (isset($this->results->error)) {
            $this->errors = $this->results->error;
            return false;
        } else {
            return true;
        }
    }

    /**
     * Tests to see if a given string is a URL.
     *
     * @param string $url The string to test.
     *
     * @return boolean True if string is URL, otherwise false.
     */
    public function isValidUrl($url) {
        return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
    }

}

class NonValidUrlException extends Exception {};

class NotAResourceException extends Exception {};

class CurlErrorException extends Exception {};