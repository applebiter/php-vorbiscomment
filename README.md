# php-vorbiscomment

VorbisComment is a wrapper around the vorbiscomment binary program
  
This class is a wrapper for the vorbiscomment binary program included with 
Xiph.or's vorbis-tools package. It exposes all of the documented program 
functionality through class methods.  

API:

    (boolean) append($comments, $escaping = false)          
        -- appends comments non-destructively
        -- takes a filename path path to a file with tags to import, or an 
            array of values 
    
    (string|boolean) getError() 
        -- returns the error message, if any
    
    (boolean) hasError() 
        -- indicates whether an error has occurred
    
    (boolean) hasStringKeys(array $array) 
        -- indicates whether a given array is an associative array 
    
    (array) list($assoc = true, $export_file = null) 
        -- lists the comments found in the audio file 
        -- optionally exports the comments to a file 
    
    (string) version() 
        -- returns version information from the vorbiscomment binary program
    
    (boolean) write($comments, $escaping = false)          
        -- replaces all existing comments
        -- takes a filename path path to a file with tags to import, or an 
            array of values 
            
Examples: 

Initialize the wrapper by passing it the absolute path to an Ogg Vorbis 
file.

    $vc = new VorbisComment('/path/to/file.ogg'); 
    
List the file's existing comments in an associative array 

    $comments = $vc->list();
    
List the file's existing comments in a numeric array 

    $comments = $vc->list(false); 
    
List the file's existing comments and also export the list to a named 
file
    
    $comments = $vc->list( 
        {true, false, or null}, 
        '/path/to/exported/list.txt' 
    );
        
Append comments to an audio file non-destructively (without changing the 
existing comments). In this example, the comments are given in an array
of name=value pairs 

    $vc->append([ 
        'title=Labyrinth', 
        'artist=Adam Rogers Quintet', 
        'genre=Jazz', 
        'date=2005', 
        'album=Apparition', 
        'tracknumber=1' 
    ]); 
        
The given array can be a nested, associative array, too, and achieve the 
same result

    $vc->append([ 
        'title' => ['Labyrinth'], 
        'artist' => ['Adam Rogers Quintet'], 
        'genre' => ['Jazz'], 
        'date' => ['2005'], 
        'album' => ['Apparition'], 
        'tracknumber' => ['1'] 
    ]); 
        
Rather than supplying the comments directly in an array, a string 
containing the path to a file containing name=value pairs may be supplied 
instead 

    $vc->append('/path/to/imported/comments.txt');
    
Files with comments to be imported should have one name=value pair per 
line
    
Write comments to a file, removing or replacing all existing comments, 
using the write() method, which works exactly the same as the append 
method 

    $vc->write([ 
        'title=Labyrinth', 
        'artist=Adam Rogers Quintet', 
        'genre=Jazz', 
        'date=2005', 
        'album=Apparition', 
        'tracknumber=1' 
    ]); 
        
Finally, hasError() returns true if an error has been generated, and 
false otherwise, whereas getError() returns the last generated error 
message 

    $error = $vc->hasError() : $vc->getError() : null; 
    