<?php 

function request (){
        return (object)$_REQUEST;
}
function requestFile(string $requetfile){
        return (object)$_FILES[$requetfile]; 
}
