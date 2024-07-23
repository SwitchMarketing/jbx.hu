<?php 

function img_src($filename) {
    return 'imgs/'.$filename;
}
function placeholder($size = null) {
    return 'https://placehold.co/' . $size;
}