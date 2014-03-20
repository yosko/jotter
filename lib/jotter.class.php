<?php

class Jotter {
    protected
        $notebooks,
        $notebooksFile,
        $notebook,
        $notebookPath,
        $notebookFile,
        $notebookName;

    public function __construct() {
        $this->notebooksFile = ROOT.'/data/notebooks.json';
    }

    /**
     * Load the list of notebooks file
     * @return array             List of notebooks (name + user)
     */
    public function loadNotebooks() {
        
        $this->notebooks = Utils::loadJson($this->notebooksFile);
        if(!is_array($this->notebooks))
            $this->notebooks = array();

        return $this->notebooks;
    }

    /**
     * Add or Edit a notebook
     * @param string  $name   New notebook name
     * @param string  $user   Owner's login
     * @param string  $editor type of editor
     * @param boolean $safe   make wysiwyg editor safe or not
     * @param boolean $public Whether the notebook should be public or private
     * @return array          List of notebooks
     */
    public function setNotebook($name, $user, $editor, $safe, $public = false) {
        if(strpos($name, '..') !== false) return false;

        $this->loadNotebooks();
        $this->notebookPath = ROOT.'/data/'.$user.'/'.$name;
        $this->notebookFile = $this->notebookPath.'/notebook.json';

        //add a new notebook
        if(!isset($this->notebooks[$user][$name])) {
            $this->notebooks[$user][$name] = true;

            //reorder notebooks
            Utils::natcaseksort($this->notebooks[$user]);

            //create the notebook directory and default note
            $defaultNote = 'note.md';
            mkdir($this->notebookPath, 0700, true);
            touch($this->notebookPath.'/'.$defaultNote);

            $this->notebook = array(
                'created'   => time(),
                'user'      => $user,
                'public'    => $public,
                'editor'    => $editor,
                'safe'      => $safe,
                'tree'      => array(
                    $defaultNote   => true
                )
            );
        } else {

        }

        $this->notebook['updated'] = time();

        //save the JSON files (notebooks list, notebook)
        Utils::saveJson($this->notebooksFile, $this->notebooks);
        Utils::saveJson($this->notebookFile, $this->notebook);

        return $this->notebooks;
    }

    /**
     * Load a notebook config file
     * @param  string $name Notebook's name
     * @param  string $user Owner's login
     * @return array        Notebook's configuration
     */
    public function loadNotebook($name, $user) {
        if(strpos($name, '..') !== false) return false;

        $this->notebookName = $name;
        $this->notebookPath = ROOT.'/data/'.$user.'/'.$this->notebookName;
        $this->notebookFile = $this->notebookPath.'/notebook.json';
        $this->notebook = Utils::loadJson($this->notebookFile);

        return $this->notebook;
    }

    /**
     * Remove a notebook and everything in it
     * @param  string $name notebook name
     * @param  string $user Owner's login
     * @return boolean      true on success
     */
    public function unsetNotebook($name, $user) {
        $this->notebooks = Utils::unsetArrayItem($this->notebooks, $user.'/'.$name);
        $absPath = ROOT.'/data/'.$user.'/'.$name.'/';

        return Utils::rmdirRecursive($absPath)
            && Utils::saveJson($this->notebooksFile, $this->notebooks);
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
        $absPath = $this->notebookPath.'/'.$path;
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
                if(!file_exists($absPath))
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
            $success = Utils::saveJson($this->notebookFile, $this->notebook);
        }

        return $success;
    }

    /**
     * Move an item to a different location
     * @param string $sourcePath path to the item to move (can be a note or directory, never empty)
     * @param string $destPath   path to destination (must be a directory or empty for the notebook root)
     */
    public function moveItem($sourcePath, $destPath) {
        $success = true;
        $itemName = basename($sourcePath);

        // if source and destination truly are different
        if($sourcePath != $destPath.'/'.$itemName) {
            //rename item
            $success = rename($this->notebookPath.'/'.$sourcePath, $this->notebookPath.'/'.$destPath.'/'.$itemName);

            //change corresponding key in tree array
            if($success) {
                $item = Utils::getArrayItem(
                    $this->notebook['tree'],
                    $sourcePath
                );
                $this->notebook['tree'] = Utils::setArrayItem(
                    $this->notebook['tree'],
                    $destPath.(!empty($destPath)?'/':'').$itemName,
                    $item
                );
                $this->notebook['tree'] = Utils::unsetArrayItem(
                    $this->notebook['tree'],
                    $sourcePath
                );

                $this->notebook['updated'] = time();
                $success = Utils::saveJson($this->notebookFile, $this->notebook);
            }
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
        $absPath = $this->notebookPath.'/'.$path;

        //strict wysiwyg (only some tags allowed)
        if((!isset($this->notebook['editor']) || $this->notebook['editor'] == 'wysiwyg')
            && (!isset($this->notebook['safe']) || $this->notebook['safe'] == true)
        ) {
            $text = $this->safeHtml($text);
        }

        return Utils::saveFile($absPath, $text);
    }

    /**
     * Load (and return) a note content
     * @param  string  $path  relative path to note
     * @param  boolean $parse force Markdown to HTML parsing
     * @return string         note content
     */
    public function loadNote($path, $parse = false) {
        $content = Utils::loadFile($this->notebookPath.'/'.$path);

        //convert Markdown to HTML
        if($content !== false && $parse) {
            $content = \Michelf\MarkdownExtra::defaultTransform($content);
        }

        return $content;
    }

    /**
     * Delete a note (file and occurence in json)
     * @param  string  $path relative path to note
     * @return boolean       true on success
     */
    public function unsetNote($path) {
        $this->notebook['tree'] = Utils::unsetArrayItem($this->notebook['tree'], $path);
        $this->notebook['updated'] = time();
        $absPath = $this->notebookPath.'/'.$path;

        return unlink($absPath)
            && Utils::saveJson($this->notebookFile, $this->notebook);
    }

    /**
     * Delete a directory (file and occurence in json) and everything in it
     * @param  string  $path relative path to directory
     * @return boolean       true on success
     */
    public function unsetDirectory($path) {
        $this->notebook['tree'] = Utils::unsetArrayItem($this->notebook['tree'], $path);
        $this->notebook['updated'] = time();
        $absPath = $this->notebookPath.'/'.$path;

        return Utils::rmdirRecursive($absPath)
            && Utils::saveJson($this->notebookFile, $this->notebook);
    }

    /**
     * Only keep allowed HTML tags
     * @param  string $html HTML input
     * @return string       HTML output
     */
    public function safeHtml($html) {
        $doc = new DOMDocument();
        $doc->loadHTML($html);

        $doc = $this->safeHtmlRecursive($doc);

        $html = preg_replace(
            '/^<!DOCTYPE.+?>/',
            '',
            str_replace(
                array('<html>', '</html>', '<body>', '</body>'),
                array('', '', '', ''),
                $doc->saveHTML()
            )
        );

        return $html;
    }

    /**
     * Recursive function to turn a DOMDocument element to an
     * @param  DomDocument $doc     Dom to convert and simplify
     * @param  DomElement  $current (for recursive purpose only) current node
     * @param  integer     $level   (for recursive purpose only) current node level
     * @return misc                 resulting DomDocument (and pointer to "current Node" in recursive call)
     */
    public function safeHtmlRecursive($doc, $current = null, $docSafe = null, $parentSafe = null, $level = 0) {
        $root = false;
        if($level == 0) {
            $root = true;
            $current = $doc;
            $docSafe = new DOMDocument();
            $parentSafe = $docSafe;
        }

        //remove these entirely
        $blacklist = array(
            'script', 'frame', 'iframe', 'frameset',
            'applet', 'object', 'embed', 'style',
            /*'form', 'fieldset', 'label',*/ 'input', 'textarea', 'button',
            'legend', 'select', 'optgroup', 'option', 'datalist', 'keygen', 'output'
        );

        //keep these
        $whiteListBlock = array(
            'p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'ol', 'ul', 'hr'
        );
        $whiteListInline = array(
            'strong', 'em'
        );

        //turn these into <p>
        $greyListBlock = array(
            'blockquote', 'code', 'pre',
            'dd', 'dt', 'dl', 'table',
            'header', 'nav', 'aside', 'section', 'article', 'footer'
        );

        //keep these and some of their attributes
        $specialList = array('a', 'img');

        //for any other tag: remove tag and keep content

        foreach ($current->childNodes as $node) {
            $currentSafe = null;
            $isTextNode = false;
            $append = true;
            $getChildren = true;
            $tag = $node->nodeName;
            $getChildren = $node->hasChildNodes();
            
            if($node->nodeType == XML_TEXT_NODE || $node->nodeType == XML_CDATA_SECTION_NODE) {
                $isTextNode = true;
                $getChildren = false;
            } elseif(in_array($tag, $blacklist)) {
                $append = false;
                $getChildren = false;
            } elseif(in_array($tag, $greyListBlock)) {
                $tag = 'p';
            } elseif(!in_array($tag, $whiteListBlock)
                && !in_array($tag, $whiteListInline)
                && !in_array($tag, $specialList)
            ) {
                $append = false;
                $currentSafe = $parentSafe;
            }

            //append node
            if($append) {
                if($isTextNode) {
                    $currentSafe = $docSafe->createTextNode( htmlentities($node->nodeValue) );
                } else {
                    $currentSafe = $docSafe->createElement($tag);
                }

                //handle special cases
                if($tag == 'a') {
                    $href = $this->safeUrl($node->getAttribute('href'));
                    $currentSafe->setAttribute('href', $href);
                } elseif($tag == 'img') {
                    $src = $this->safeUrl($node->getAttribute('src'));
                    $currentSafe->setAttribute('src', $src);
                }

                $parentSafe->appendChild($currentSafe);
            }

            //recursive call
            if($getChildren) {
                $this->safeHtmlRecursive($doc, $node, $docSafe, $currentSafe, $level+1);
            }
        }

        if($level == 0) {
            return $docSafe;
        }
    }

    /**
     * Only keep allowed HTML tags
     * @param  string $html HTML input
     * @return string       HTML output
     */
    public function safeUrl($url) {
        $whiteListProtocol = array(
            'http', 'https', 'ftp', 'mailto', 'data'
        );

        $parsed = parse_url($url);
        if($parsed == false ||
            isset($parsed['scheme']) && !in_array($parsed['scheme'], $whiteListProtocol)
        ) {
            $url = '';
        }

        return $url;
    }
}

?>