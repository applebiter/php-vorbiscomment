<?php 
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/** 
 * VorbisComment is a wrapper around the vorbiscomment binary program
 * 
 * This class is a wrapper for the vorbiscomment binary program included with 
 * Xiph.or's vorbis-tools package. It exposes all of the documented program 
 * functionality through class methods.  
 * 
 * API:
 * 
 *     (boolean) append($comments, $escaping = false)          
 *         -- appends comments non-destructively
 *         -- takes a filename path path to a file with tags to import, or an 
 *            array of values 
 *     
 *     (string|boolean) getError() 
 *         -- returns the error message, if any
 *     
 *     (boolean) hasError() 
 *         -- indicates whether an error has occurred
 *     
 *     (boolean) hasStringKeys(array $array) 
 *         -- indicates whether a given array is an associative array 
 *     
 *     (array) list($assoc = true, $export_file = null) 
 *         -- lists the comments found in the audio file 
 *         -- optionally exports the comments to a file 
 *     
 *     (string) version() 
 *         -- returns version information from the vorbiscomment binary program
 *     
 *     (boolean) write($comments, $escaping = false)          
 *         -- replaces all existing comments
 *         -- takes a filename path path to a file with tags to import, or an 
 *            array of values 
 *            
 * Examples: 
 * 
 *     Initialize the wrapper by passing it the absolute path to an Ogg Vorbis 
 *     file.
 *     
 *         $vc = new VorbisComment('/path/to/file.ogg'); 
 *         
 *     List the file's existing comments in an associative array 
 *     
 *         $comments = $vc->list();
 *         
 *     List the file's existing comments in a numeric array 
 *     
 *         $comments = $vc->list(false); 
 *         
 *     List the file's existing comments and also export the list to a named 
 *     file
 *         
 *         $comments = $vc->list( 
 *             {true, false, or null}, 
 *             '/path/to/exported/list.txt' 
 *         );
 *         
 *     Append comments to an audio file non-destructively (without changing the 
 *     existing comments). In this example, the comments are given in an array
 *     of name=value pairs 
 *     
 *         $vc->append([ 
 *             'title=Labyrinth', 
 *             'artist=Adam Rogers Quintet', 
 *             'genre=Jazz', 
 *             'date=2005', 
 *             'album=Apparition', 
 *             'tracknumber=1' 
 *         ]); 
 *         
 *     The given array can be a nested, associative array, too, and achieve the 
 *     same result
 *     
 *         $vc->append([ 
 *             'title' => ['Labyrinth'], 
 *             'artist' => ['Adam Rogers Quintet'], 
 *             'genre' => ['Jazz'], 
 *             'date' => ['2005'], 
 *             'album' => ['Apparition'], 
 *             'tracknumber' => ['1'] 
 *         ]); 
 *         
 *     Rather than supplying the comments directly in an array, a string 
 *     containing the path to a file containing name=value pairs may be supplied 
 *     instead 
 *     
 *         $vc->append('/path/to/imported/comments.txt');
 *         
 *     Files with comments to be imported should have one name=value pair per 
 *     line
 *         
 *     Write comments to a file, removing or replacing all existing comments, 
 *     using the write() method, which works exactly the same as the append 
 *     method 
 *     
 *         $vc->write([ 
 *             'title=Labyrinth', 
 *             'artist=Adam Rogers Quintet', 
 *             'genre=Jazz', 
 *             'date=2005', 
 *             'album=Apparition', 
 *             'tracknumber=1' 
 *         ]); 
 *         
 *     Finally, hasError() returns true if an error has been generated, and 
 *     false otherwise, whereas getError() returns the last generated error 
 *     message 
 *     
 *         $error = $vc->hasError() : $vc->getError() : null; 
 *         
 *         
 *            
 * PHP version 7 
 * 
 * @category Command-line, Ogg Vorbis 
 * @package applebiter/vorbiscomment 
 * @author Richard Lucas <webmaster@applebiter.com> 
 * @link https://bitbucket.org/applebiter/vorbiscomment
 * @license MIT License 
 * 
 * The MIT License (MIT)
 *
 * Copyright (c) 2018
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class VorbisComment 
{
    /**
     * _errors 
     * 
     * Holds error message if any is generated
     * 
     * @var string
     */
    protected $_error = null;
    
    /**
     * _has_error 
     * 
     * Indicates whether an error has been generated
     * 
     * @var boolean
     */
    protected $_has_error = false;
    
    /**
     * _audio_file 
     * 
     * The absolute path to the file to be inspected/manipulated
     * 
     * @var string
     */
    protected $_audio_file;
    
    /**
     * _path_to_binary 
     * 
     * The path to the binary program vorbiscomment
     * 
     * @var string
     */
    protected $_path_to_binary;
    
    /**
     * __construct() 
     * 
     * Takes the path to the input file to be inspected/manipulated, and 
     * optionally takes the path to the vorbiscomment binary program
     * 
     * @param string $filenamepath 
     * @param string $binarypath
     */
    public function __construct($filenamepath, $binarypath = '/usr/bin/vorbiscomment') 
    {
        if (is_file($filenamepath)) {
            
            if (is_readable($filenamepath)) { 
                
                $this->_audio_file = $filenamepath;
            }
            else { 
                
                $this->_error = 'The file is not readable.';
                $this->_has_error = true;
            }
        }
        else {
            
            $this->_error = 'The supplied filename is not a file.';
            $this->_has_error = true;
        }
        
        $this->_path_to_binary = $binarypath;
    }
    
    /**
     * append() 
     * 
     * Append comments to a file, non-destructively added to existing comments
     * 
     * The $comments argument can either be a string or an array. If a string is 
     * supplied, it is assumed to be the absolute path to a text file which will 
     * supply the name=value pairs to be appended to the audio file. If an array 
     * is supplied, it is assumed to be a list of name => value pairs to be 
     * appended to the audio file.
     * 
     * If the input is a filename, then the input file is expected to contain 
     * one name=value pair per line
     * 
     * Sescaping is a boolean determining whether to use \n-style escapes to 
     * allow multiline comments.
     * 
     * @param string or array $comments 
     * @param boolean $escaping optional
     * @return boolean true on success, false otherwise
     */
    public function append($comments, $escaping = false) 
    {
        if (is_string($comments)) {
            
            if (is_file($comments)) {
                
                if (is_readable($comments)) { 
                    
                    $contents = file($comments, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    
                    if (!empty($contents)) {
                        
                        /* Append the comments found in the supplied file to the 
                         * audio file.
                         */
                        
                        $escaping = $escaping ? ' -e' : '';
                        $command = "{$this->_path_to_binary}{$escaping} -a \"{$this->_audio_file}\" -c \"{$comments}\"";
                        $output = [];
                        $stderr = null;
                        
                        exec(escapeshellcmd($command), $output, $stderr);
                        
                        return true;
                    }
                    else { 
                        
                        $this->_error = 'The comments input file is empty.';
                        $this->_has_error = true;
                        
                        return false;
                    }
                } 
                else {
                    
                    $this->_error = 'The comments input file is not readable.';
                    $this->_has_error = true;
                    
                    return false;
                }
            }
            else {
                
                $this->_error = 'The comments input file does not exist.';
                $this->_has_error = true;
                
                return false;
            }
        } 
        elseif (is_array($comments) && !empty($comments)) {
            
            /* Append the comments found in the supplied array to the
             * audio file.
             */
            
            $escaping = $escaping ? '-e' : '';
            $output = [];
            $stderr = null;
            
            // Begin assembling the command here            
            $command = "{$this->_path_to_binary} {$escaping} -a \"{$this->_audio_file}\"";
            
            $associative = $this->hasStringKeys($comments);
            
            foreach ($comments as $name => $value) {
                
                /* Trickiness here, to be flexible and adaptive, as $name might 
                 * be numeric, indicating a flattened form of input array rather 
                 * than an associative array form. If the input array is 
                 * associative, its values are themselves going to be arrays of 
                 * strings. Furthermore, if the array is associative, then 
                 * $value might not be an array at all, but rather a string 
                 * value. If the array has numeric keys, then the name=value 
                 * pairs are the values of the array, in the form NAME=value
                 */
                
                // $value could be either an array of strings or a string
                
                if ($associative) {
                    
                    $uc_name = filter_var(strval($name), FILTER_SANITIZE_STRING);
                    
                    if (is_string($value) && !empty($value)) {
                        
                        /* Condition: An associative array of one dimension was 
                         * supplied, having string values
                         */
                        
                        $value = filter_var(strval($value), FILTER_SANITIZE_STRING);
                        
                        $command .= " -t \"{$uc_name}={$value}\"";
                    }
                    else {
                        
                        if (!is_array($value) || empty($value)) {
                            
                            $this->_error = 'The supplied array of comments was empty. (1)';
                            $this->_has_error = true;
                            
                            return false;
                        }
                        
                        foreach ($value as $multival) {
                            
                            /* Condition: An associative array of two dimensions 
                             * was supplied, having arrays of strings for values
                             */
                            
                            $multival = filter_var(strval($multival), FILTER_SANITIZE_STRING);
                            
                            $command .= " -t \"{$uc_name}={$multival}\"";
                        }
                    }
                } 
                else {
                    
                    if (is_string($value) && !empty($value)) {
                        
                        list($inner_name, $inner_value) = explode('=', $value);
                        
                        $inner_name = filter_var(strval($inner_name), FILTER_SANITIZE_STRING);
                        $inner_value = filter_var(strval($inner_value), FILTER_SANITIZE_STRING);
                        $command .= " -t \"{$inner_name}={$inner_value}\"";
                    }
                }
                
                
            }
            
            exec(escapeshellcmd($command), $output, $stderr);
            
            return true;
        }
        else {
            
            $this->_error = 'The supplied array of comments was empty. (2)';
            $this->_has_error = true;
            
            return false;
        }
    }
    
    /**
     * getError() 
     * 
     * Return the error, if any exists, and boolean false otherwise
     * 
     * @return string|boolean
     */
    public function getError() 
    {
        return $this->_has_error ? $this->_error : false;
    }
    
    /**
     * hasError() 
     * 
     * Indicates whether an error has occurred
     * 
     * @return boolean
     */
    public function hasError() 
    {
        return $this->_has_error;
    }
    
    /**
     * hasStringKeys() 
     * 
     * Indicates whether the given array is associative or has numeric keys
     * 
     * @param array $array
     * @return boolean
     */
    public function hasStringKeys(array $array) 
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
    
    /**
     * list() 
     * 
     * Returns an array of comments found in the audio file
     * 
     * @param boolean $assoc optional flag indicating which format to return
     * @return array
     */
    public function list($assoc = true, $export_file = null) 
    {
        $export = $export_file ? " -c \"{$export_file}\"" : '';
        $command = "{$this->_path_to_binary} -l \"{$this->_audio_file}\"{$export}";
        $output = [];
        $stderr = 0;
        
        exec(escapeshellcmd($command), $output, $stderr);
        
        if (!$assoc) {
            
            return $output;
        }
        
        $comments = [];
        
        foreach ($output as $pair) {
            
            list($name, $value) = explode('=', $pair);
            
            $comments[trim($name)][] = trim($value);
        }
        
        return $comments;
    }
    
    /** 
     * version() 
     * 
     * Returns the version information from the vorbiscomment binary program
     * 
     * @return string
     */
    public function version() 
    {
        $vorbiscomment = Configure::read('Lab.Binaries.vorbiscomment');
        $command = "{$vorbiscomment} --version 2>&1";
        $result = shell_exec($command);
        
        return $result;
    }
    
    /**
     * write() 
     * 
     * Write comments to a file, replacing existing comments
     * 
     * The $comments argument can either be a string or an array. If a string is 
     * supplied, it is assumed to be the absolute path to a text file which will 
     * supply the name=value pairs to be written to the audio file. If an array 
     * is supplied, it is assumed to be a list of name => value pairs to be 
     * written to the audio file.
     * 
     * If the input is a filename, then the input file is expected to contain 
     * one name=value pair per line
     * 
     * Sescaping is a boolean determining whether to use \n-style escapes to 
     * allow multiline comments.
     * 
     * @param string or array $comments 
     * @param boolean $escaping optional
     * @return boolean true on success, false otherwise
     */
    public function write($comments, $escaping = false) 
    {
        if (is_string($comments)) {
            
            if (is_file($comments)) {
                
                if (is_readable($comments)) {
                    
                    $contents = file($comments, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    
                    if (!empty($contents)) {
                        
                        /* Append the comments found in the supplied file to the
                         * audio file.
                         */
                        
                        $escaping = $escaping ? ' -e' : '';
                        $command = "{$this->_path_to_binary}{$escaping} -w \"{$this->_audio_file}\" -c \"{$comments}\"";
                        $output = [];
                        $stderr = null;
                        
                        exec(escapeshellcmd($command), $output, $stderr);
                        
                        return true;
                    }
                    else {
                        
                        $this->_error = 'The comments input file is empty.';
                        $this->_has_error = true;
                        
                        return false;
                    }
                }
                else {
                    
                    $this->_error = 'The comments input file is not readable.';
                    $this->_has_error = true;
                    
                    return false;
                }
            }
            else {
                
                $this->_error = 'The comments input file does not exist.';
                $this->_has_error = true;
                
                return false;
            }
        }
        elseif (is_array($comments) && !empty($comments)) {
            
            /* Append the comments found in the supplied array to the
             * audio file.
             */
            
            $escaping = $escaping ? '-e' : '';
            $output = [];
            $stderr = null;
            
            // Begin assembling the command here
            $command = "{$this->_path_to_binary} {$escaping} -w \"{$this->_audio_file}\"";
            
            $associative = $this->hasStringKeys($comments);
            
            foreach ($comments as $name => $value) {
                
                /* Trickiness here, to be flexible and adaptive, as $name might
                 * be numeric, indicating a flattened form of input array rather
                 * than an associative array form. If the input array is
                 * associative, its values are themselves going to be arrays of
                 * strings. Furthermore, if the array is associative, then
                 * $value might not be an array at all, but rather a string
                 * value. If the array has numeric keys, then the name=value
                 * pairs are the values of the array, in the form NAME=value
                 */
                
                // $value could be either an array of strings or a string
                
                if ($associative) {
                    
                    $uc_name = filter_var(strval($name), FILTER_SANITIZE_STRING);
                    $uc_name = $uc_name;
                    
                    if (is_string($value) && !empty($value)) {
                        
                        /* Condition: An associative array of one dimension was
                         * supplied, having string values
                         */
                        
                        $value = filter_var(strval($value), FILTER_SANITIZE_STRING);
                        
                        $command .= " -t \"{$uc_name}={$value}\"";
                    }
                    else {
                        
                        if (!is_array($value) || empty($value)) {
                            
                            $this->_error = 'The supplied array of comments was empty. (1)';
                            $this->_has_error = true;
                            
                            return false;
                        }
                        
                        foreach ($value as $multival) {
                            
                            /* Condition: An associative array of two dimensions
                             * was supplied, having arrays of strings for values
                             */
                            
                            $multival = filter_var(strval($multival), FILTER_SANITIZE_STRING);
                            
                            $command .= " -t \"{$uc_name}={$multival}\"";
                        }
                    }
                }
                else {
                    
                    if (is_string($value) && !empty($value)) {
                        
                        list($inner_name, $inner_value) = explode('=', $value);
                        
                        $inner_name = filter_var(strval($inner_name), FILTER_SANITIZE_STRING);
                        $inner_value = filter_var(strval($inner_value), FILTER_SANITIZE_STRING);
                        $command .= " -t \"{$inner_name}={$inner_value}\"";
                    }
                }
                
                
            }
            
            exec(escapeshellcmd($command), $output, $stderr);
            
            return true;
        }
        else {
            
            $this->_error = 'The supplied array of comments was empty. (2)';
            $this->_has_error = true;
            
            return false;
        }
    }
}