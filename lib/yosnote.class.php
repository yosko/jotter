<?php

class YosNote {
    protected
        $notebooks,
        $notebooksFile,
        $notebook,
        $notebookFile,
        $notebookName;

    public function __construct() {
        $this->notebooksFile = ROOT.'/notebooks/notebooks.json';
    }

    /**
     * Load the list of notebooks file
     * @param  integer $userId (Optional) filter on notebooks of the given user
     * @return array           List of notebooks (name + user)
     */
    public function loadNotebooks($userId = -1) {
        
        $this->notebooks = $this->loadJson($this->notebooksFile);
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
            $defaultNote = 'note.md';
            mkdir($notebookPath);
            touch($notebookPath.'/'.$defaultNote);

            $this->notebook = array(
                'created'   => time(),
                'user'      => $user,
                'public'    => $public,
                'tree'      => array(
                    $defaultNote   => true
                )
            );
        } else {

        }

        $this->notebook['updated'] = time();

        //save the JSON files (notebooks list, notebook)
        $this->saveJson($this->notebooksFile, $this->notebooks);
        $this->saveJson($this->notebookFile, $this->notebook);

        return $this->notebooks;
    }

    /**
     * Load a notebook config file
     * @param  string  $name   Notebook's name
     * @param  integer $userId (Optional) filter on notebooks of the given user
     * @return array           Notebook's configuration
     */
    public function loadNotebook($name, $userId = -1) {
        $this->notebookName = $name;
        if(strpos($name, '..') !== false) return false;

        $this->notebook = $this->loadJson(ROOT.'/notebooks/'.$name.'/notebook.json');

        return $this->notebook;
    }

    public function setDirectory($path, $newName = false) {
        $absPath = ROOT.'/notebooks/'.$this->notebookName.'/'.$path;
        if(!file_exists($absPath))
            mkdir($absPath, 0700, true);

        $notebookPath = ROOT.'/notebooks/'.$this->notebookName;
        $this->notebookFile = $notebookPath.'/notebook.json';

        $this->notebook['tree'] = Utils::setArrayItem($this->notebook['tree'], $path, array());

        return
            file_exists($absPath) && is_dir($absPath)
            && !empty($this->notebook['tree'])
            && $this->saveJson($this->notebookFile, $this->notebook);
    }

    public function setNote($path, $newName = false, $data = false) {
        $path .= '.md';
        $absPath = ROOT.'/notebooks/'.$this->notebookName.'/'.$path;

        //if necessary, create parent directories
        if(!file_exists(dirname($absPath)))
            mkdir(dirname($absPath), 0700, true);

        //create the file
        touch($absPath);

        $notebookPath = ROOT.'/notebooks/'.$this->notebookName;
        $this->notebookFile = $notebookPath.'/notebook.json';

        $this->notebook['tree'] = Utils::setArrayItem($this->notebook['tree'], $path, true);

        return
            file_exists($absPath)
            && !empty($this->notebook['tree'])
            && $this->saveJson($this->notebookFile, $this->notebook);
    }

    public function loadNote($path) {
        return $this->loadFile(ROOT.'/notebooks/'.$this->notebookName.'/'.$path);
    }

    protected function loadFile($file) {
        if (file_exists( $file )) {
            $data = file_get_contents($file);
            return $data;
        } else {
            // touch($file);
            return false;
        }
    }

    /**
     * Load a (compressed) JSON file
     * @param  string  $file     Path to file
     * @param  boolean $compress If data should be gzip uncompressed before decoding it
     * @return misc              File content decoded
     */
    protected function loadJson($file, $uncompress = false) {
        if($data = $this->loadFile($file)) {
            if($uncompress)
                $data = gzinflate($data);
            $data = json_decode($data, true);
        }
        return $data;
    }

    /**
     * Save data into a compressed JSON file
     * @param  string  $file     Path to file
     * @param  misc    $data     Content to save into file
     * @param  boolean $compress Compress (or not) file content in gzip
     * @return boolean           true on success
     */
    protected function saveJson($file, $data, $compress = false) {
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