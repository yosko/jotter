<?php

class Login extends YosLogin implements YosLTSession {
    protected $users;

    /**
     * Load complete list of users
     * @return array list of users
     */
    public function loadUsers() {
        if(!isset($this->users)) {
            $userFile = ROOT.'/data/users.json';
            $this->users = Utils::loadJson($userFile);
        }

        if(!is_array($this->users))
            $this->users = array();
    }

    /**
     * Add or edit a user and save it to the users file
     * @param  string  $login    login
     * @param  string  $password password (will be hashed)
     * @return boolean           exec status
     */
    public function setUser($login, $password) {
        $userFile = ROOT.'/data/users.json';
        $newUser = array(
            'login' => $login,
            'password' => YosLoginTools::hashPassword($password)
        );
        $this->loadUsers();

        $foundUser = false;
        foreach($this->users as $key => $user) {
            //edit existing user
            if($user['login'] == $login) {
                $foundUser = true;
                $this->users[$key] = $newUser;
            }
        }

        //add new user
        if(!$foundUser) {
            $this->users[] = $newUser;
            usort($this->users, function($a, $b) {
                return strcmp($a['login'], $b['login']);
            });
        }

        return Utils::saveJson($userFile, $this->users);
    }

    /**
     * Add a user (only calls setUser)
     * @param  string  $login    login
     * @param  string  $password password (will be hashed)
     * @return boolean           exec status
     */
    public function createUser($login, $password) {
        return $this->setUser($login, $password);
    }

    public function deleteUser($login) {
        $userFile = ROOT.'/data/users.json';
        foreach($this->users as $key => $user) {
            if($user['login'] == $login) {
                unset($this->users[$key]);
            }
        }
        return Utils::saveJson($userFile, $this->users);
    }

    /**
     * Get a user by his(her) login
     * @param  string $login login
     * @return array         user
     */
    protected function getUser($login) {
        $foundUser = false;
        $this->loadUsers();

        foreach($this->users as $user) {
            if($user['login'] == $login) {
                $foundUser = $user;
            }
        }

        return $foundUser;
    }

    /**
     * Return the complete list of users
     * @return array users list
     */
    public function getUsers() {
        return $this->users;
    }

    function setLTSession($login, $sid, $value) {
        //create the session directory if needed
        if(!file_exists($this->LTDir)) { mkdir($this->LTDir, 0700, true); }

        $fp = fopen($this->LTDir.$login.'_'.$sid.'.ses', 'w');
        fwrite($fp, gzdeflate(json_encode($value)));
        fclose($fp);
    }

    function getLTSession($cookieValue) {
        $value = false;
        $file = $this->LTDir.$cookieValue.'.ses';
        if (file_exists($file)) {

            //unset long-term session if expired
            if(filemtime($file)+$this->LTDuration <= time()) {
                unsetLTSession($cookieValue);
                $value = false;
            } else {
                $value = json_decode(gzinflate(file_get_contents($file)), true);
                //update last access time on file
                touch($file);
            }
        }
        return($value);
    }

    function unsetLTSession($cookieValue) {
        $filePath = $this->LTDir.$cookieValue.'.ses';
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    function unsetLTSessions($login) {
        $files = glob( $this->LTDir.$login.'_*', GLOB_MARK );
        foreach( $files as $file ) {
            unlink( $file );
        }
    }

    function flushOldLTSessions() {
        $dir = $this->LTDir;

        //list all the session files
        $files = array();
        if ($dh = opendir($dir)) {
            while ($file = readdir($dh)) {
                if(!is_dir($dir.$file)) {
                    if ($file != "." && $file != "..") {
                        $files[$file] = filemtime($dir.$file);
                    }
                }
            }
            closedir($dh);
        }

        //sort files by date (descending)
        arsort($files);

        //check each file
        $i = 1;
        foreach($files as $file => $date) {
            if ($i > $this->nbLTSession || $date+$this->LTDuration <= time()) {
                $this->unsetLTSession(basename($file));
            }
            ++$i;
        }
    }
}

?>