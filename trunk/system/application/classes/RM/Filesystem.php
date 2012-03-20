<?php
/**
 * ResMania - Reservation System Framework http://resmania.com
 * Copyright (C) 2011  ResMania Ltd.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 *
 * Class for wrapp any PHP filesystem related function that we use
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */

class RM_Filesystem
{
    /**
     * Scan folder and returns all filenames
     *
     * @param string $folder path to folder
     * @param array $allowedExtensions this is an array containing allowable filetypes if null filter will not be applied, this should be a list of extensions as a strings in lowercase
     * @return array - list of all file names, except .svn files
     */
    static function getFiles($folder, $allowedExtensions = null)
    {
        $files = array();
        if (is_dir($folder) == false) return $files;

        if ($handle = opendir($folder)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != ".svn") {
                    if ($allowedExtensions == null) {
                        $files[] = $file;
                    } else {
                        $chunks = explode('.', $file);
                        $extension = strtolower($chunks[count($chunks) - 1]);
                        if (in_array($extension, $allowedExtensions)) {
                            $files[] = $file;
                        }
                    }
                }
            }
        }
        closedir($handle);
        return $files;
    }


    /**
     * Returns all paths to files recursively from the parent folder.
     *
     * @static
     * @param  $folder
     * @param array $allowedExtensions
     * @return array - array with FULL physical paths to files
     */
    static function getFilesRecursively($folder, $allowedExtensions = array('php'))
    {
        $files = array();
        if (is_dir($folder) == false) return $files;

        if ($handle = opendir($folder)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != ".svn") {
                    $fullPath = $folder.DIRECTORY_SEPARATOR.$file;
                    if (is_dir($fullPath)){
                        $files = array_merge($files, self::getFilesRecursively($fullPath, $allowedExtensions));                       
                    }
                    if ($allowedExtensions == null) {
                        $files[] = $fullPath;
                    } else {
                        $chunks = explode('.', $file);
                        $extension = strtolower($chunks[count($chunks) - 1]);
                        if (in_array($extension, $allowedExtensions)) {
                            $files[] = $fullPath;
                        }
                    }
                }
            }
        }
        closedir($handle);
        return $files;
    }

    /**
     * Empty folder - removes all files from it (except .svn)
     *
     * @param string $folder full folder path
     * @return boolean - true if success
     */
    static function emptyFolder($folder)
    {
        $files = self::getFiles($folder);
        foreach ($files as $key => $value) {
            $files[$key] = $folder.DIRECTORY_SEPARATOR.$files[$key];
        }
        return self::deleteFiles($files);
    }

    /**
     * Delete a file.
     *
     * @param string $file full file path
     * @return bool
     */
    static function deleteFile($file)
    {
        return unlink($file);
    }

    /**
     * Delete files passed in an array
     *
     * @param array $files  an array of files that need to be deleted
     * @return boolean - true if success
     */
    static function deleteFiles($files){

        $result=true;
        if (!is_array($files)) return false;

        foreach ($files as $file){
            $result = self::deleteFile($file);
        }
        return $result; // if delete of any of the files fails return false

    }

    /**
     * Delete recursivly folder/file in the system
     *
     * @param string $foldername - full folder/file path
     * @return bool
     */
    public static function deleteFolder($foldername)
    {
        if (is_file($foldername)) {
            return RM_Filesystem::deleteFile($foldername);
        }
        if (is_dir($foldername)) {
            if ($handle = opendir($foldername)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        RM_Filesystem::deleteFolder($foldername.DIRECTORY_SEPARATOR.$file);
                    }
                }
            }
            closedir($handle);
            rmdir($foldername);
        }
        return true;
    }

    /**
     * Alternative for PHP Copy, using fopen
     *
     * @param   string  $file_source    source file, including full path
     * @param   string  $file_target    destination file, including full path
     * @return  bool    true = copied ok
     */
    function fileDownload($file_source, $file_target) {
        $rh = fopen($file_source, 'rb');
        $wh = fopen($file_target, 'wb');
        if ($rh===false || $wh===false) {
        // error reading or opening file
           return false;
        }
        while (!feof($rh)) {
            if (fwrite($wh, fread($rh, 1024)) === FALSE) {
                   // 'Download error: Cannot write to file ('.$file_target.')';
                   return false;
               }
        }
        fclose($rh);
        fclose($wh);
        // No error
        return true;
    }

    /**
     * Get a remote file size in bytes or readable english syntax
     *
     * @param   string  $url    url to file to get size of
     * @param   bool    $readable   English readable output, otherwise result is in bytes
     */
    function getRemoteFileSize($url, $readable = true) {
        $parsed = parse_url($url);
        $host = $parsed["host"];
        $fp = @fsockopen($host, 80, $errno, $errstr, 20);
        if(!$fp) return false;
        else {
            @fputs($fp, "HEAD $url HTTP/1.1\r\n");
            @fputs($fp, "HOST: $host\r\n");
            @fputs($fp, "Connection: close\r\n\r\n");
            $headers = "";
            while(!@feof($fp))$headers .= @fgets ($fp, 128);
        }
        @fclose ($fp);
        $return = false;
        $arr_headers = explode("\n", $headers);
        foreach($arr_headers as $header) {
        // follow redirect
            $s = 'Location: ';
            if(substr(strtolower ($header), 0, strlen($s)) == strtolower($s)) {
                $url = trim(substr($header, strlen($s)));
                return $this->get_remote_file_size($url, $readable);
            }

            // parse for content length
            $s = "Content-Length: ";
            if(substr(strtolower ($header), 0, strlen($s)) == strtolower($s)) {
                $return = trim(substr($header, strlen($s)));
                break;
            }
        }
        if($return && $readable) {
            $size = round($return / 1024, 2);
            $sz = "KB"; // Size In KB
            if ($size > 1024) {
                $size = round($size / 1024, 2);
                $sz = "MB"; // Size in MB
            }
            $return = "$size $sz";
        }
        return $return;
    }

    /**
     * unZip uncompresses a file using the PCL Zip Library.
     *
     * @params  string  $source     the source path
     * @params  string  $destination    the destination path
     * @return  bool    True on Success
     */
    function unZip($source, $destination) {

        $zip = new PclZip($source);

        if ($zip->extract(PCLZIP_OPT_PATH, $destination) == 0) {
            RM_Log::toLog("Unzip Failed: ".$zip->errorInfo(true));
            return false;
        }

        return true;

    }

    /**
     * Copy folder recursive (with all sub folders and files)
     *
     * @params string $source - the source folder
     * @params string $destination - the destination folder
     * @params array $skipSources - [optional][default=array()] array of relative paths (from RM root) of
     * files and folders that need to be skipped while copy process
     * @return bool
     */
    public function recursivecopy($source, $destination, &$skipSources = array()) {
        $return = true;

        $rmConfig = new RM_Config();
        $chmodOctal = intval($rmConfig->getValue('rm_config_chmod_value'),8);

        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            $fullPathDestination = $destination.DIRECTORY_SEPARATOR.$entry;
            $skipped = false;
            foreach ($skipSources as $skipSource) {
                if (strpos($fullPathDestination, $skipSource) !== false){
                    $skipped = true;
                    break;
                }
            }
            if ($skipped) {
                continue;
            }

            $fullPathEntry = $source.DIRECTORY_SEPARATOR.$entry;
            if (is_file($fullPathEntry)) {
                $return &= copy($fullPathEntry, $fullPathDestination);                
            } elseif (is_link($fullPathEntry)) {
                $return &= symlink(readlink($fullPathEntry), $fullPathDestination);
            } elseif (is_dir($fullPathEntry)) {
                if (!is_dir($fullPathDestination)) {
                    $return &= mkdir($fullPathDestination);
                    chmod($fullPathDestination, $chmodOctal); // allows deletion via ftp
                }
                // Deep copy directories using recursion
                $return &= $this->recursivecopy(
                    $fullPathEntry,
                    $fullPathDestination,
                    $skipSources
                );
            }
        }

        $dir->close();
        return $return;
    }
}