<?php

class YosNote {
    protected $notebooks;
    protected $notebook;

    public function __construct() {

    }

    /**
     * Load the list of notebooks file
     * @param  integer $userId (Optional) filter on notebooks of the given user
     * @return array           List of notebooks (name + user)
     */
    public function loadNotebooks($userId = -1) {
        $file = ROOT.'/notebooks/notebooks.json.gz';
        $this->notebooks = $this->loadFile($file);

        return $this->notebooks;
    }

    /**
     * Load a notebook config file
     * @param  string  $name   Notebook's name
     * @param  integer $userId (Optional) filter on notebooks of the given user
     * @return array           Notebook's configuration
     */
    public function loadNotebook($name, $userId = -1) {
        if(strpos($name, '..') !== false) return false;
        $file = ROOT.'/notebooks/notebooks.json.gz';
        $this->notebooks = $this->loadFile($file);

        return $this->notebooks;
    }

    /**
     * Load a compressed JSON file
     * @param  string  $file     Path to file
     * @param  boolean $compress If data should be gzip uncompressed before decoding it
     * @return misc              File content decoded
     */
    protected function loadFile($file, $uncompress = true) {
        if (file_exists( $file )) {
            $data = file_get_contents($file);
            if($uncompress)
                $data = gzinflate($data);
            return json_decode($data, true);
        } else {
            touch($file);
            return false;
        }
    }

    /**
     * Save data into a compressed JSON file
     * @param  string  $file     Path to file
     * @param  misc    $data     Content to save into file
     * @param  boolean $compress Compress (or not) file content in gzip
     * @return boolean           true on success
     */
    private function saveFile($file, $data, $compress = true) {
        $fp = fopen( $file, 'w' );
        if($fp) {
            if(version_compare(PHP_VERSION, '5.4.0') >= 0)
                $json = json_encode($data, JSON_PRETTY_PRINT);
            else
                $json = json_encode($data);
            if($compress)
                $json = gzdeflate($json);
            fwrite($fp, $json);
            fclose($fp);
        }
        return $fp !== false;
    }
}

?>