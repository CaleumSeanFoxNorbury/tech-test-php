<?php

class PropertyIndexController {
    public function propertyIndex() {
        // render view 
        ob_start();
        include "view/property-index.html";
        $html = ob_get_clean();
        echo $html;
    }
}