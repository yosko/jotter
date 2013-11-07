<?php

class YosNote {
    protected
        $notebooks,
        $notebooksFile,
        $notebook,
        $notebookPath,
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
        if(strpos($name, '..') !== false) return false;
        $this->notebookFile = $this->notebookPath.'/notebook.json';

        //add a new notebook
        if(!isset($this->notebooks[$name])) {
            $this->notebooks[$name] = array(
                'user' => $user
            );

            //create the notebook directory and default note
            $defaultNote = 'note.md';
            mkdir($this->notebookPath);
            touch($this->notebookPath.'/'.$defaultNote);

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
        if(strpos($name, '..') !== false) return false;

        $this->notebookName = $name;
        $this->notebookPath = ROOT.'/notebooks/'.$this->notebookName;
        $this->notebookFile = $this->notebookPath.'/notebook.json';
        $this->notebook = $this->loadJson($this->notebookFile);

        return $this->notebook;
    }

    /**
     * Add or edit (rename) an item (note or directory)
     * @param string   $path    Relative path to item
     * @param boolean  $isDir   False for notes, true for directories
     * @param string   $newName New name for item, false if not needed
     * @param boolean  $data    Data to keep inside JSON array for notes (unused)
     * @return boolean          True on success
     */
    public function setItem($path, $isDir = true, $newName = false, $data = false) {
        $success = true;
        $absPath = ROOT.'/notebooks/'.$this->notebookName.'/'.$path;
        $dirPath = $isDir?$absPath:dirname($absPath);

        //if necessary, create parent directories
        if(!file_exists(dirname($absPath)))
            $success = mkdir(dirname($absPath), 0700, true);

        if($success && $newName !== false) {
            //rename item
            $success = rename($absPath, dirname($absPath).'/'.$newName);

            //change corresponding key in tree array
            $item = Utils::getArrayItem(
                $this->notebook['tree'],
                $path
            );
            $this->notebook['tree'] = Utils::setArrayItem(
                $this->notebook['tree'],
                (dirname($path)!='.'?dirname($path).'/':'').$newName,
                $item
            );
            $this->notebook['tree'] = Utils::unsetArrayItem(
                $this->notebook['tree'],
                $path
            );

        //create the item
        } elseif($success) {
            if($isDir) {
                $success = mkdir($absPath, 0700, true);
                $value = array();
            } else {
                $success = touch($absPath);
                $value = true;
            }

            $this->notebook['tree'] = Utils::setArrayItem($this->notebook['tree'], $path, $value);
        }

        //save notebook.json file
        if($success) {
            $this->notebook['updated'] = time();
            $success = $this->saveJson($this->notebookFile, $this->notebook);
        }

        return $success;
    }

    /**
     * Add or edit (rename) a directory
     * @param string $path    Relative path to directory
     * @param string $newName New name for directory, false if not needed
     * @return boolean        True on success
     */
    public function setDirectory($path, $newName = false) {
        return $this->setItem($path, true, $newName);
    }

    /**
     * Add or edit (rename) a note
     * @param string $path    Relative path to note (with extension)
     * @param string $newName New name for note (with extension), false if not needed
     * @return boolean        True on success
     */
    public function setNote($path, $newName = false, $data = false) {
        return $this->setItem($path, false, $newName, $data);
    }

    /**
     * Set the text of a note
     * @param string $path Relative path to note (with extension)
     * @return boolean     True on success
     */
    public function setNoteText($path, $text) {
        $absPath = ROOT.'/notebooks/'.$this->notebookName.'/'.$path;

        //convert HTML to Markdown
        $markdown = new HTML_To_Markdown($text);

        return $this->saveFile($absPath, $markdown);
    }

    /**
     * Load (and return) a note content
     * @param  string $path relative path to note
     * @return string       note content
     */
    public function loadNote($path) {
        $content = $this->loadFile(ROOT.'/notebooks/'.$this->notebookName.'/'.$path);

        //convert Markdown to HTML
        return \Michelf\MarkdownExtra::defaultTransform($content);
    }

    /**
     * Delete a note (file and occurence in json)
     * @param  string  $path relative path to note
     * @return boolean       true on success
     */
    public function unsetNote($path) {
        $this->notebook['tree'] = Utils::unsetArrayItem($this->notebook['tree'], $path);
        $absPath = ROOT.'/notebooks/'.$this->notebookName.'/'.$path;

        return unlink($absPath)
            && $this->saveJson($this->notebookFile, $this->notebook);
    }

    /**
     * Delete a directory (file and occurence in json) and everything in it
     * @param  string  $path relative path to directory
     * @return boolean       true on success
     */
    public function unsetDirectory($path) {
        $this->notebook['tree'] = Utils::unsetArrayItem($this->notebook['tree'], $path);
        $absPath = ROOT.'/notebooks/'.$this->notebookName.'/'.$path;

        return $this->rmdirRecursive($absPath)
            && $this->saveJson($this->notebookFile, $this->notebook);
    }

    /**
     * Load a text file
     * @param  string $file path to file
     * @return string       file content
     */
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
     * Save a text file
     * @param  string $file    path to file
     * @param  string $content file content
     * @return boolean         true on success
     */
    protected function saveFile($file, $content) {
        if (!file_exists( $file )) {
            touch($file);
        }
        $fp = fopen( $file, 'w' );
        if($fp) {
            fwrite($fp, $content);
            fclose($fp);
        }
        return $fp !== false;
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
        if(version_compare(PHP_VERSION, '5.4.0') >= 0)
            $json = json_encode($data, JSON_PRETTY_PRINT);
        else
            $json = json_encode($data);

        if($compress)
            $json = gzdeflate($json);

        return $this->saveFile($file, $json);
    }

    /**
     * Recursive directory remove
     * taken from http://www.php.net/manual/en/function.rmdir.php#110489
     * @param  string $dir path to directory
     * @return boolean     true on success
     */
    public static function rmdirRecursive($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            if(is_dir("$dir/$file"))
                rmdirRecursive("$dir/$file");
            else
                unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}

?>