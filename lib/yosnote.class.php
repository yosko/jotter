<?php

class YosNote {
    protected $notebooks, $notebooksFile, $notebook, $notebookFile;

    public function __construct() {
        $this->notebooksFile = ROOT.'/notebooks/notebooks.json';
    }

    /**
     * Load the list of notebooks file
     * @param  integer $userId (Optional) filter on notebooks of the given user
     * @return array           List of notebooks (name + user)
     */
    public function loadNotebooks($userId = -1) {
        
        $this->notebooks = $this->loadFile($this->notebooksFile);
        if(!is_array($this->notebooks))
            $this->notebooks = array();

        return $this->notebooks;
    }

    /**
     * Add or Edit a notebook
     * @param string  $name   New notebook name
     * @param integer $user   Owner's id (required for new notebook)
     * @param boolean $public Whether the notebook should be public or private
     * @return array          List of notebooks
     */
    public function setNotebook($name, $user = -1, $public = false) {
        $notebookPath = ROOT.'/notebooks/'.$name;
        $this->notebookFile = $notebookPath.'/notebook.json';

        //add a new notebook
        if(!isset($this->notebooks[$name])) {
            $this->notebooks[$name] = array(
                'user' => $user
            );

            //create the notebook directory and default note
            mkdir($notebookPath);
            touch($notebookPath.'/note.md');

            $this->notebook = array(
                'created'   => time(),
                'user'      => $user,
                'public'    => $public
            );
        } else {

        }

        $this->notebook['updated'] = time();

        //save the JSON files (notebooks list, notebook)
        $this->saveFile($this->notebooksFile, $this->notebooks);
        $this->saveFile($this->notebookFile, $this->notebook);

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
        $this->notebooks = $this->loadFile($this->notebooksFile);

        return $this->notebooks;
    }

    /**
     * Load a compressed JSON file
     * @param  string  $file     Path to file
     * @param  boolean $compress If data should be gzip uncompressed before decoding it
     * @return misc              File content decoded
     */
    protected function loadFile($file, $uncompress = false) {
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
    protected function saveFile($file, $data, $compress = false) {
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