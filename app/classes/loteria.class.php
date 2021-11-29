<?php
namespace SimonOravec\Loteria;

class Loteria {

    /**
     * Variables
     */
    private $data_url = 'https://www.mfsr.sk/components/mfsrweb/winners/data-ajax.jsp';
    private $config_file = __DIR__.'/../config.json';

    private $data;
    private $config;

    /**
     * Constructor
     */
    function __construct()
    {
        $this->config = $this->getConfig();
    }
    
    /**
     * Load configuration file
     * Create configuration file if it doesn't exits
     * @return array
     */
    private function getConfig()
    {
        if (!file_exists($this->config_file)) {
            $defaultConfig = ["codes"=>array()];
            $this->config = $defaultConfig;
            $this->writeConfig();
            return $defaultConfig;
        } else {
            return json_decode(file_get_contents($this->config_file), true);
        }
    }

    /**
     * Write configuration file
     * @return boolean If the write was successful
     */
    private function writeConfig()
    {
        if (!file_put_contents($this->config_file, json_encode($this->config, JSON_PRETTY_PRINT))) {
            return false;
        }
        return true;
    }

    /**
     * Load lottery data from MFSR
     * @return array Lottery data
     */
    public function loadDataFromMFSR()
    {
        $data = json_decode(file_get_contents($this->data_url), true);
        $this->data = $this->optimizeData($data);
    }

    /**
     * Optimize data received from MFSR because they are complicated to search otherwise
     * @param array $data Data loaded from MFSR
     * 
     * @return array Optimized data
     */
    private function optimizeData($data)
    {
        $optimized = array();
        foreach ($data as $item) {
            $key = $item['kod'];
            unset($item['kod']);

            $optimized[$key] = $item;
        }

        return $optimized;
    }

    /**
     * Check if the code has won
     * @param mixed $code The lottery code
     * 
     * @return boolean If the code has won
     */
    public function checkCode($code)
    {
        if (!isset($this->data[$code])) return true;
        return false;
    }

    /**
     * Get the winner data from the code, if the code doesn't exist, returns null
     * @param string $code The lottery code
     * 
     * @return mixed
     */
    public function getCodeData($code) 
    {
        if (!isset($this->data[$code])) return null;
        return $this->data[$code];
    }

    /**
     * Check if the code is valid
     * @param string $code The lottery code
     * 
     * @return [type]
     */
    public function validateCode($code) 
    {
        if (strlen($code) != 14) return false;

        $regex = '^[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}^';
        preg_match($regex, $code, $matches);

        if (sizeof($matches) != 1) return false;

        return true;
    }

    /**
     * Check all the keys from the configuration and return if they won
     * If the key won, also return winner data
     * @return array
     */
    public function checkCodes() 
    {
        $keys = array();
        foreach ($this->config['codes'] as $key) {
            if (isset($this->data[$key]))
            {
                $keys[$key] = ['win'=>true, 'data'=>$this->data[$key]];
            }
            else
            {
                $keys[$key] = ['win'=>false, 'data'=>null];
            }
        }

        return $keys;
    }

    /**
     * Adds a new code to the configuration file
     * @param string $code
     * 
     * @return boolean If the code was added
     */
    public function addCode($code)
    {
        if (in_array($code, $this->config['codes']) || !$this->validateCode($code)) return false;

        array_push($this->config['codes'], $code);

        $this->writeConfig();

        return true;
    }

    public function removeCode($code)
    {
        if (!in_array($code, $this->config['codes'])) return false;

        $arr_key = array_search($code, $this->config['codes']);
        unset($this->config['codes'][$arr_key]);

        $this->writeConfig();

        return true;
    }

}