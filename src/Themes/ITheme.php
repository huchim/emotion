<?php namespace Emotion\Themes;

interface iTheme {
    public function Disable();
    public function Enable();
    public function OpenHeader();
    public function CloseHeader();
    public function OpenBody($events = "");
    public function CloseBody();    
    public function insertFile($jsfile);
    public function getFragment($fragmentName);
}