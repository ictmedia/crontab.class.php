<?php
/**
 * Class for easy maintaining cron tables using crontab command
 * on Unix/Linux OS
 *
 * @author ICT & MEDIA, s.r.o.
 * @author Jan Lipovský <janlipovsky@gmail.com>
 * @link www.ictmedia.cz
 * @copyright (c) 2014, ICT & MEDIA, s.r.o.
 * @license BSD 3-Clause (LICENSE file is located in the root folder of this project)
 * 
 * @file crontab.class.php
 * @brief Crontab class
 * @details Class for easy maintaining cron tables using crontab command on Unix/Linux OS.
 * 
 * @version 0.2
 * @date 2014-07-02
 */
class crontab {

    private $crontab_path = '';     // path to crontab (e.g. /usr/bin/)
    private $crontab = 'crontab';   // crontab command to exec
    private $cron_table = array();  // cron table lines in array
    private $is_loaded = false;     // is cron table loaded
    private $error_msg = '';        // crontab error message

    /**
     * Class constructor
     * @param string $crontabPath   - path to crontab command
     */
    function __construct($crontabPath = '') {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            echo 'ERROR: WINDOWS server is not supperted. Please use UNIX like server.';
            exit(-1);
        }

        if (!empty($crontabPath)) {
            if (substr($crontabPath, -1) !== "/") {
                $crontabPath .= "/";
            }
            if (is_dir($crontabPath)) {
                $this->setCrontabPath($crontabPath);
            } else {
                echo 'ERROR: Path "' . $crontabPath . '" is not directory.';
                exit(-2);
            }
        }

        if (!$this->commandExist($this->crontab_path . $this->crontab)) {
            echo 'ERROR: Command "' . $this->crontab_path . $this->crontab . '" does NOT exist. Please install crontab package.';
            exit(-3);
        }
    }

    /**
     * Function returns true - if command exist;
     * false - if command does not exist
     * @return string
     */
    private function commandExist($cmd) {
        $returnVal = shell_exec("which $cmd");
        return (empty($returnVal) ? false : true);
    }

    /**
     * Function returns user name
     * @return string
     */
    public function whoami() {
        if (commandExist("whoami")) {
            return shell_exec("whoami");
        } else {
            return false;
        }
    }

    /**
     * Function sets path to "crontab" command
     * Returns TRUE on success; false on failure
     * 
     * @param string $path
     * @return boolean*
     */
    public function setCrontabPath($path) {
        if (!empty($path)) {
            if (is_dir($path)) {
                $this->crontab_path = $path;
                return true;
            } else {
                exit('Error: Wrong path "' . $path . '". Please select a directory.');
                return false;
            }
        }
    }

    /**
     * Tries to run crontab command
     * 
     * @param type $param       - parameters passed to crontab
     * @param array $output     - crontab lines on success, empty array on failure
     * @param string $prePIPE   - commands before crontab connected with pipe "|"
     * @return boolean          - true on success, false on failure
     */
    private function execCrontab($param, &$output, $prePIPE = '') {
        $return_var = 0;
        if (!empty($prePIPE)) {
            $prePIPE .= ' | ';
        }
        $command = $prePIPE . $this->crontab_path . $this->crontab . ' ' . $param . ' 2>&1';
        $ret = exec($command, $output, $return_var);
        if ($return_var != 0) {
            $error = "Error #" . $return_var . ' [' . $ret . '] - while trying to exec: "' . $command . '"';
            $this->error_msg = $error . '<br />';
            foreach ($output as $line) {
                $this->error_msg .= $line . '<br/>';
            }
            if ($return_var != 1) {
                exit($error);
            } else {
                $output = array();
                return true;  // ERROR == 1 -> no crontab for "user"
            }
        } else {
            return true;
        }
    }

    /**
     * Function loads cron table using "crontab -l" command
     * @return boolean* - true on success, false on failure
     */
    public function load() {
        $crontab = array();
        if ($this->execCrontab('-l', $crontab)) {
            $this->cron_table = $crontab;
            $this->is_loaded = true;
            return true;
        } else {
            $this->is_loaded = false;
            return false;
        }
    }

    /**
     * Returns crontable as text or false on failure
     * 
     * @param string $delimiter
     * @return string | boolean
     */
    public function getCronTableTXT($delimiter = '\n') {
        if (!$this->is_loaded) {
            if (!$this->load()) {
                return false;
            }
        }

        $ret = '';
        foreach ($this->cron_table as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $ret .= $line . $delimiter;
            }
        }

        return $ret;
    }

    /**
     * Returns crontable as array or false
     * @return array | boolean
     */
    public function getCronTable() {
        if ($this->is_loaded) {
            return $this->cron_table;
        } else {
            if ($this->load()) {
                return $this->cron_table;
            } else {
                return false;
            }
        }
    }

    /**
     * Alias for addCron 
     * @param string $cron
     */
    public function appendCronJob($cron) {
        return $this->addCronJob($cron);
    }

    /**
     * Adds cron job to cron table using crontab
     * @param string $cron
     */
    public function addCronJob($cron) {
        if (!$this->is_loaded) {
            if (!$this->load()) {
                return false;
            }
        }

        $cronJobs = '';
        foreach ($this->cron_table as $line) {
            $tmp = trim($line);
            if (!empty($tmp)) {
                $cronJobs .= $tmp . "\n";
            }
        }

        $cronJobs .= trim($cron) . "\n";
        $pre = 'echo -n "' . $cronJobs . '"';
        $out = array();
        $this->is_loaded = false;
        return $this->execCrontab('', $out, $pre);
    }

    /**
     * Saves cron job at specific position (and overwrites that line) in crontab
     * @param string $cronJob
     * @param int $pos
     * @return boolean
     */
    public function saveCronJob($cronJob, $pos = -1) {
        if ($pos < 0) {
            return $this->addCronJob($cronJob);
        }

        if (!$this->is_loaded) {
            if (!$this->load()) {
                return false;
            }
        }

        $cronJobs = '';
        $posCount = 0;
        foreach ($this->cron_table as $line) {
            if ($pos == $posCount) {
                $cronJobs .= $cronJob . "\n";
                $cronJobs .= $line . "\n";
            } else {
                $cronJobs .= $line . "\n";
            }
            $posCount++;
        }
        
        if($posCount <= $pos) {
            return $this->addCronJob($cronJob);
        }

        $pre = 'echo -n "' . $cronJobs . '"';
        $out = array();
        $this->is_loaded = false;
        return $this->execCrontab('', $out, $pre);
    }

    /**
     * Saves array as crontable
     * 
     * @param array $cronTableArray - cron table in array; each item one line
     * @return type
     */
    public function saveCronTable($cronTableArray) {
        $cronJobs = '';
        foreach ($cronTableArray as $line) {
            $cronJobs .= $line . "\n";
        }

        $pre = 'echo -n "' . $cronJobs . '"';
        $out = array();
        $this->is_loaded = false;
        return $this->execCrontab('', $out, $pre);
    }

    /**
     * Deletes line at "$lineNumber" position from cron table
     * @param int $lineNumber
     */
    public function deleteCronJob($lineNumber) {
        if ($lineNumber >= 0) {
            if (!$this->is_loaded) {
                if (!$this->load()) {
                    return false;
                }
            }

            $cronJobs = '';
            $posCount = 0;
            foreach ($this->cron_table as $line) {
                if ($lineNumber != $posCount) {
                    $cronJobs .= $line . "\n";
                }
                $posCount++;
            }

            $pre = 'echo -n "' . $cronJobs . '"';
            $out = array();
            $this->is_loaded = false;
            return $this->execCrontab('', $out, $pre);
        }
    }

    /**
     * Remove crontab file - dele
     * @param int $lineNumber
     */
    public function clearCronTable() {
            return $this->execCrontab('-r', $out, '');
    }

    /**
     * Returns number of line containing string $contains
     * Returns FALSE on failure
     * 
     * @param string $contains
     * @return boolean
     */
    public function getCronJobNumber($contains = '') {
        if (!empty($contains)) {
            if (!$this->is_loaded) {
                if (!$this->load()) {
                    return false;
                }
            }

            $posCount = 0;
            foreach ($this->cron_table as $line) {
                if (strpos($line, $contains) !== false) {
                    return $posCount;
                }
                $posCount++;
            }
            return false;
        }
    }

    /**
     * Returns line of cron table containing "$contains" string
     * or false when string is not found
     * @return string
     */
    public function getCronJob($contains = '') {
        if (!empty($contains)) {
            if (!$this->is_loaded) {
                if (!$this->load()) {
                    return false;
                }
            }

            foreach ($this->cron_table as $line) {
                if (strpos($line, $contains) !== false) {
                    return $line;
                }
            }
            return false;
        }
    }

    /**
     * Returns line of cron table at $position
     * or false when string is not found
     * @return string
     */
    public function getCronJobAt($position = -1) {
        if ($position >= 0) {
            if (!$this->is_loaded) {
                if (!$this->load()) {
                    return false;
                }
            }

            if ($position < count($this->cron_table)) {
                return $this->cron_table[$position];
            } else {
                return false;
            }
        }
    }

    /**
     * Returns array whit parsed cron tab line 
     * (min, hour, day, month, day of the week, program)
     * 
     * @param string $cronJob
     * @return array
     */
    public function parseCronJob($cronJob = '') {
        $ret = array('min' => 'error');
        $cronJob = trim($cronJob);
        if (!empty($cronJob)) {
            $tmp = explode(' ', $cronJob, 6);
            $ret['min'] = $tmp[0];
            $ret['hour'] = $tmp[1];
            $ret['day'] = $tmp[2];
            $ret['month'] = $tmp[3];
            $ret['dotw'] = $tmp[4];
            $ret['command'] = $tmp[5];
        } else {
            return false;
        }

        return $ret;
    }

    /**
     * Returns error message set while executing crontab command
     * @return string
     */
    public function getError() {
        return $this->error_msg;
    }

}
