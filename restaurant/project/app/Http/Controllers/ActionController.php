<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ActionController extends Controller
{
    function index() {
        /*@ Base64 image code */
        $data = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAPoAAAD6BAMAAAB6wkcOAAAAG1BMVEX///8AAACfn59fX18fHx/f39+/v79/f38/Pz9KdSATAAAACXBIWXMAAA7EAAAOxAGVKw4bAAACeklEQVR4nO3WQU8aQRTA8WFxdj26uiw9QqIJR5vaxmPRJnBcWmuvSxNJj9gYy1G0rX7svjc7W5YApu1yav6/hOU5vMzbN8wsGgMAAAAAAAAAAAAAAAAAAAAAAAAAAPA/CE7i80zee0+iI0H0PXnzTF6gefc6Muuf5zWLh8nXSW9fgtNvQmaz724/z6eb85qPkncrwcVg0mvXrd6Ry7VUffADOz+kv/3NebvHfmQoK9Ht1CzvKsqMjz6+1uU92Zy369clasml+XYL1SOZZOBj9z7qbMzb8dXdu11dpH+rflaEYarXYnlt7kay5er+xsbuwwdTn87quwj2ygGJ3Z2Mp0t5DV+96643W6iuraZ2ok1Gd4t7cLssbC/nNfLLK41nlXuoR1bRtudxIl02XfWw5cajdKl1t9qjWRzr3jgtRjJT25N8x8mReT+U6seV6tJ8tXXNM6OzzB7KLb7aVvVAa+Xyupj6/VZWj9Jq6y4vlJcdlPttC9XHHR/IrlpaeWm+vS5PN9u2Vt6el5EU9dX3yoLpujwt6qvXLW4uymensak/a8VVN7xu+5U8M8r9Wat94uxwEbb8mhcroBs+StfkmVHm1/y0bvVKS3rM3VPHP051w/9uvpKnx7zR0WBg6gkX36Zr2f3AdIvNpBu+bL6apy27H5igZeqpHqluLosqf9tipxdn3Te/dPRSnzNa/UfgrwQHH0VmgvvMfNAJw4PMHN4V1d3cUV7NM70jY1/qB+PXJhiun/SP7caqZexN3H/h6nyJ+2u+zTLPfOon8U93b/Mkqdl6xeWkDK6ezbOTvAy28JAHAAAAAAAAAAAAAAAAAAAAAAAAAAArfgEnYGI6+pXm3AAAAABJRU5ErkJggg==";    
    
        // Calling function auto generate unique name
        $this->tf_convert_base64_to_image( $data, '../uploads/' );
    }
    
     
    function tf_convert_base64_to_image( $base64_code, $path, $image_name = null ) {
         
        if ( !empty($base64_code) && !empty($path) ) {
     
            // split the string to get extension and remove not required part
            // $string_pieces[0] = to get image extension
            // $string_pieces[1] = actual string to convert into image
            $string_pieces = explode( ";base64,", $base64_code);
     
            /*@ Get type of image ex. png, jpg, etc. */
            // $image_type[1] will return type
            $image_type_pieces = explode( "image/", $string_pieces[0] );
     
            $image_type = $image_type_pieces[1];
     
            /*@ Create full path with image name and extension */
            $store_at = $path.md5(uniqid()).'.'.$image_type;
     
            /*@ If image name available then use that  */
            if ( !empty($image_name) ) {
                $store_at = $path.$image_name.'.'.$image_type;
            }
     
            $decoded_string = base64_decode( $string_pieces[1] );
     
            file_put_contents( $store_at, $decoded_string );
     
        }   
     
    }
     
    
}