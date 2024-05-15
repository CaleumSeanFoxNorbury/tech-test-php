<?php

class PropertyIndexController {
    
    // todo :: add contrctor for passing in edit ids!!

    public function propertyIndex() {
        // render view 
        ob_start();
        include "view/property-index.html";
        $html = ob_get_clean();
        echo $html;
    }

}