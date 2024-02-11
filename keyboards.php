<?php
function Keyboard_Main(){
    return array(
                    array(
                        array(
                            'action' => array(
                                "type" => "text",
                                "payload" => array(
                                    "rep" => "/справка"
                                ),
                                "label" => "Справка"
                            ),
                            'color' => 'positive'
                        ),
                    ),
                    /*array(
                        array(
                            'action' => array(
                                "type" => "text",
                                "payload" => array(
                                    "rep" => "/привет"
                                ),
                                "label" => "Мое имя"
                            ),
                            'color' => 'positive'
                        ),
                        array(
                            'action' => array(
                                "type" => "text",
                                "payload" => array(
                                    "rep" => "/кто я"
                                ),
                                "label" => "Моя роль"
                            ),
                            'color' => 'positive'
                        ),
                    ),*/                    
                    array(
                        array(
                            'action' => array(
                                "type" => "text",
                                "payload" => array(
                                    "rep" => "/гей бар"
                                ),
                                "label" => "Гей бар"
                            ),
                            'color' => 'primary'
                        ),
                    ),
                );
}
?>
