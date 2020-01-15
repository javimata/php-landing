<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
/**
 * RandomString function generate a random string
 *
 * @param integer $length - is the long of the string
 * @param boolean $uc     - add a case range letters
 * @param boolean $n      - add numbers
 * @param boolean $sc     - add special characters
 * @return void
 */
function RandomString($length=10,$uc=TRUE,$n=FALSE,$sc=FALSE) {

    $source = 'abcdefghijklmnopqrstuvwxyz';
    if($uc==1) $source .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if($n==1) $source .= '1234567890';
    if($sc==1) $source .= '|@#~$%()=^*+[]{}-_';
    if($length>0){
        $rstr = "";
        $source = str_split($source,1);
        for($i=1; $i<=$length; $i++){
            mt_srand((double)microtime() * 1000000);
            $num = mt_rand(1,count($source));
            $rstr .= $source[$num-1];
        }
 
    }
    return $rstr;

}


/**
 * cleanHTML function that clean a string
 *
 * @param string  $text - Text to clear
 * @return void
 */
function cleanHTML( $text = "" ) {

    $strS = array('<br>','<br />','<br/>');
    $strL = array('');

    $text = str_replace($strS,$strL,$text);

    return $text;

}

/**
 * cleanString function that clean a string
 *
 * @param string  $text - Text to clear
 * @return void
 */
function cleanString( $text="" ) {

    $strS = array('(',')',' ','-','•');
    $strL = array('');

    $text = str_replace($strS,$strL,$text);

    return $text;

}


/**
 * get Configuration json
 *
 * @return void
 */
function getConfig( $sub = NULL ) {

    $file = (!$sub) ? "config.json" : "../config.json" ;
    $jsonStr = file_get_contents( $file );
    $config  = json_decode( $jsonStr );

    return $config;

}


/**
 * validate Actual URL
 */
function validateUrl( $url = NULL ){

    if ( $url == "" ) {

        $url = $_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI];

    }

    return $url;

}

/**
 * Crea un menu desde la información del config.json
 */
function createMenu( $menu = NULL, $tipo = NULL ) {

    if ( count($menu) > 0 ) {

        $class = ( $menu->class != "" ) ? 'class="'.$menu->class.'" ' : "";
        $id    = ( $menu->id != "" ) ? 'id="'.$menu->id.'" ' : "";
        if ( $tipo == NULL ) {
            $type = ( $menu->type != "list" ) ? $menu->type : "list";
        } else {
            $type = $tipo;
        }
        $menuHTML = "";

        if ( $type != "li" ){

            $menuHTML .= ( $menu->type !== "list" ) ? "<div " : "<ul ";
            $menuHTML .= $class . $id . ">";

        }

        foreach ($menu->items as $key => $item) {

            if ( $type == "list" || $type ="li" ) { 
                $menuHTML .= "<li ";
                if ( $item->class != "" ) { $menuHTML .= 'class="'.$item->class.'"'; }
                $menuHTML .= ">";
            }

            $itemClass = ( $item->itemClass != "" ) ? 'class="'.$item->itemClass.'"' : "";
            $target    = ( $item->target != "" ) ? 'target="'.$item->target.'"' : "";
            $menuHTML .= '<a href="'.$item->url.'" '.$itemClass.' '.$target.'>'.$key.'</a>';

            if ( $type == "list" || $type ="li" ) {
                $menuHTML .= "</li>";
            }
            
        }

        if ( $type != "li" ){
            $menuHTML .= ( $type == "link" ) ? "</div> " : "</ul>";
        }

    }

    return $menuHTML;

}

/**
 * crea un formulario pasando el objeto del mismo a la funcion
 */
function createForm( $form = NULL ){

    if ( $form ) {
        // var_dump($form);
        $action         = ($form->action != "") ? $form->action : "php/process.php";
        $method         = ($form->method != "") ? $form->method : "POST";
        $class          = ($form->class != "") ? $form->class : "";
        $id             = ($form->id != "") ? $form->id : "";
        $containerClass = ($form->containerClass != "") ? $form->containerClass : "";
        $containerId    = ($form->containerId != "") ? $form->containerId : "";
        $attribs        = ($form->attribs != "") ? $form->attribs : "";
        $mailchimpList  = ($form->mailchimpList != "") ? $form->mailchimpList : "";

        $formHTML = '';
        $formHTML .= '<form action="'.$action.'" method="'.$method.'"';
        if ( $class != "" ) { $formHTML .= ' class="' . $class . '"'; }
        if ( $id != "" ) { $formHTML .= ' id="' . $id . '"'; }
        if ( $mailchimpList != "" ) { $formHTML .= ' data-mclist="' . $mailchimpList . '"'; }

        if ( count( $form->attribs ) > 0 ){ $formHTML .= addAttribs($form->attribs); }

        $formHTML .= '>';
        $formHTML .= '<div'; // Container div
        if ( $containerClass != "" ) { $formHTML .= ' class="' . $containerClass . '"'; }
        if ( $containerId != "" ) { $formHTML .= ' id="' . $containerId . '"'; }
        $formHTML .= '>';
 
        // $fields = $form->fields;
        $formHTML .= createField($form->fields);

        if ( $form->errorBox != "" ) { 
            $formHTML .= '<div class="'.$form->errorBox.'">&nbsp;</div>';
        }

        $formHTML .= '</div>';
        $formHTML .= '</form>';
        echo $formHTML;

    }

}

/**
 * Crea campos, recibe un objeto $fields
 */
function createField( $fields ){

    $fieldsHTML = '';
    foreach ($fields as $key => $field) {
        
        $fieldsHTML .= '<div ';
        if ($field->class != "") { $fieldsHTML .= ' class="'.$field->class.'"'; }
        if ($field->id != "") { $fieldsHTML .= ' id="'.$field->id.'"'; }
        $fieldsHTML .= '>';
        $fieldsHTML .= createFieldbyType($field);
        $fieldsHTML .= '</div>';
        
    }

    return $fieldsHTML;

}

/**
 * Crea los campos en base al tipo del mismo
 */
function createFieldbyType( $fieldMain ){

    $fieldHTML = '';
    $field = $fieldMain->attribs;

    switch ($field->type) {
        case 'text':
        case 'email':
        case 'tel':
        case 'date':
        default;

            $placeholder = ($field->required == 1) ? $field->placeholder . " *" : $field->placeholder;

            if ( $field->label ) {
                $fieldHTML .= '<label for="'.$field->id.'">'.$field->label.'</label>';
            }

            $fieldHTML .= '<input type="'.$field->type.'" ';
            $fieldHTML .= 'name="'.$fieldMain->name.'" ';
            $fieldHTML .= ($field->placeholder) ? 'placeholder="'.$placeholder.'" ': '' ;
            $fieldHTML .= ($field->class) ? 'class="'.$field->class.'" ': '' ;
            $fieldHTML .= ($field->id) ? 'id="'.$field->id.'" ': '' ;
            $fieldHTML .= ($field->value) ? 'value="'. replaceValues($field->value).'" ': '' ;
            $fieldHTML .= ($field->required == 1) ? 'required ' : '';
            if ( count( $field->attribs ) > 0 ){ $fieldHTML .= addAttribs($field->attribs); }

            $fieldHTML .= '/>';
            break;
        
        case 'hidden':

            $fieldHTML .= '<input type="'.$field->type.'" ';
            $fieldHTML .= 'name="'.$fieldMain->name.'" ';
            $fieldHTML .= ($field->class) ? 'class="'.$field->class.'" ': '' ;
            $fieldHTML .= ($field->id) ? 'id="'.$field->id.'" ': '' ;
            $fieldHTML .= 'value="'. replaceValues($field->value).'" ';
            if ( count( $field->attribs ) > 0 ){ $fieldHTML .= addAttribs($field->attribs); }
            $fieldHTML .= '/>';
            break;

        case 'select':

            if ( $field->label ) {
                $fieldHTML .= '<label for="'.$field->id.'">'.$field->label.'</label>';
            }

            $fieldHTML .= '<select type="'.$field->type.'" ';
            $fieldHTML .= 'name="'.$fieldMain->name.'" ';
            $fieldHTML .= ($field->class) ? 'class="'.$field->class.'" ': '' ;
            $fieldHTML .= ($field->id) ? 'id="'.$field->id.'" ': '' ;
            if ( count( $field->attribs ) > 0 ){ $fieldHTML .= addAttribs($field->attribs); }
            $fieldHTML .= ($field->required == 1) ? 'required ' : '';
            $fieldHTML .= '>';

            $fieldHTML .= ($field->placeholder) ? '<option value="">'.$field->placeholder.'</option>': '</option>- Selecciona -</option>' ;

            $valores = explode("|",$field->value);

            foreach($valores as $valor) {

                $fieldHTML .= '<option value="';
                $fieldHTML .= str_replace('[*]','',$valor) . '" ';
                if (strpos($valor, '[*]') !== false) {
                    $fieldHTML .= 'selected';
                }
                $fieldHTML .= '>';
                $fieldHTML .= str_replace('[*]','',$valor);
                $fieldHTML .= '</option>';

            }

            $fieldHTML .= '</select>';
    
            break;

        case 'estado':

            $estados = "Aguascalientes,Baja California,Baja California Sur,Campeche,Chiapas,Chihuahua,Coahuila,Colima,Durango,Estado de México,Guanajuato,Guerrero,Hidalgo,Jalisco,Michoacán,Morelos,Nayarit,Nuevo León,Oaxaca,Puebla,Querétaro,Quintana Roo,San Luis Potosí,Sinaloa,Sonora,Tabasco,Tamaulipas,Tlaxcala,Veracruz,Yucatán,Zacatecas";

            if ( $field->label ) {
                $fieldHTML .= '<label for="'.$field->id.'">'.$field->label.'</label>';
            }

            $fieldHTML .= '<select ';
            $fieldHTML .= 'name="'.$fieldMain->name.'" ';
            $fieldHTML .= ($field->class) ? 'class="'.$field->class.'" ': '' ;
            $fieldHTML .= ($field->id) ? 'id="'.$field->id.'" ': '' ;
            if ( count( $field->attribs ) > 0 ){ $fieldHTML .= addAttribs($field->attribs); }
            $fieldHTML .= ($field->required == 1) ? 'required ' : '';
            $fieldHTML .= '>';

            $fieldHTML .= ($field->placeholder) ? '<option value="">'.$field->placeholder.'</option>': '</option>- Selecciona -</option>' ;

            if ( $field->value != "" ) {
                $fieldHTML .= '<option value="'.$field->value.'">'.$field->value.'</option>';
            }

            $valores = explode(",",$estados);

            foreach($valores as $valor) {

                $fieldHTML .= '<option value="';
                $fieldHTML .= str_replace('[*]','',$valor) . '" ';
                if (strpos($valor, '[*]') !== false) {
                    $fieldHTML .= 'selected';
                }
                $fieldHTML .= '>';
                $fieldHTML .= str_replace('[*]','',$valor);
                $fieldHTML .= '</option>';

            }

            $fieldHTML .= '</select>';            
            break;

        case 'textarea':

            if ( $field->label ) {
                $fieldHTML .= '<label for="'.$field->id.'">'.$field->label.'</label>';
            }

            $placeholder = ($field->required == 1) ? $field->placeholder . " *" : $field->placeholder;
            $fieldHTML .= '<textarea name="'.$fieldMain->name.'" ';
            $fieldHTML .= ($field->placeholder) ? 'placeholder="'.$placeholder.'" ': '' ;
            $fieldHTML .= ($field->class) ? 'class="'.$field->class.'" ': '' ;
            $fieldHTML .= ($field->id) ? 'id="'.$field->id.'" ': '' ;
            $fieldHTML .= ($field->required == 1) ? ' required' : '';
            $fieldHTML .= '>';
            $fieldHTML .= ($field->value) ? 'value="'.$field->value.'" ': '' ;
            $fieldHTML .= '</textarea>';
            
            break;
            
        case 'radio':
        case 'checkbox':

            if ( $field->label ) {
                $fieldHTML .= '<label class="'.$field->classLabel.'">'.$field->label.'</label>';
            }

            $fieldHTML .= '<div class="'.$field->classContainerValues.'">';

            $valores = explode("|",$field->value);

            foreach($valores as $valor) {

                $fieldHTML .= '<div class="'.$field->classContainer.'">';
                $fieldHTML .= '<input class="'.$field->class.'" type="'.$field->type.'" name="'.$field->id.'" id="'.$field->id.$valor.'" value="'.$valor.'" '; 
                if (strpos($valor, '[*]') !== false) {
                    $fieldHTML .= 'checked';
                }
                $fieldHTML .= ($field->required == 1) ? ' required' : '';
                $fieldHTML .= '>';

                $fieldHTML .= '<label class="form-check-label" for="'.$field->id.$valor.'">';
                $fieldHTML .= str_replace('[*]','',$valor);
                $fieldHTML .= '</label>';
                $fieldHTML .= '</div>';
                
            }

            $fieldHTML .= '</div>';
            break;
            
        case 'title':
            $tag = ( $field->tag != "" ) ? $field->tag : "p";
            $fieldHTML .= '<'.$tag;
            $fieldHTML .= ( $field->class != "" ) ? ' class="'.$field->class.'" ' : "" ;
            $fieldHTML .= '>';
            $fieldHTML .= $field->value;
            $fieldHTML .= '</' . $tag . '>';
            break;

        case 'submit':
        case 'button':

            $fieldHTML .= '<button type="'.$field->type.'" ';
            $fieldHTML .= 'name="'.$fieldMain->name.'" ';
            $fieldHTML .= ($field->class) ? 'class="'.$field->class.'" ': '' ;
            $fieldHTML .= ($field->id) ? 'id="'.$field->id.'" ': '' ;
            if ( count( $field->attribs ) > 0 ){ $fieldHTML .= addAttribs($field->attribs); }            
            $fieldHTML .= '>';
            $fieldHTML .= ($field->value) ? $field->value : '' ;
            $fieldHTML .= '</button>';
            
    }

    return $fieldHTML;

}

/**
 * Agrega la lista de atributos
 * opcion nokey agrega un atributo sin key
 */
function addAttribs($attribs){

    if ($attribs) {

        $attribsHTML = '';
        foreach ($attribs as $key => $attrib) {

            if ( $key == 'nokey' ){
                
                $attribsHTML .= ' '.replaceValues($attrib).' ';
                
            } else {
                
                $attribsHTML .= $key . '="'.replaceValues($attrib).'"';

            }

        }

        return $attribsHTML;

    }

}

/**
 * replaceValues Reemplaza valores de un string
 * @param string $value Texto a reemplazar
 */
function replaceValues( $value = NULL ) {

    $config = getConfig();
    $arrayBase    = array('[Y]','[NOW]','[TOMORROW]','[company]');
    $arrayReplace = array(date('Y'), date('Y-m-d'),date('Y-m-d', strtotime("+ 1 day")),'<a href="#" target="_blank">'.$config->info->titulo.'</a>');
    $value = str_replace( $arrayBase, $arrayReplace, $value );

    return $value;

}

/**
 * configure Forms
 * return fonts
 */
function configFonts() {

    $config = getConfig();
    $fonts  = "";

    foreach( $config->configuracion->fonts as $key => $font ): 
        $fonts .=  str_replace(" ","+",$key); 
        if ( $font->weight != "") { $fonts .= ":" . str_replace(" ","",$font->weight); }
        $fonts .= "|";    
    endforeach;

    return str_replace(" ", "+",trim($fonts,"|"));

}

/**
 * Slug
 * @param string string for slugged
 */
function createSlug($string) {

    $table = array(
            'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
            'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
            'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
            'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
            'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', '/' => '-', ' ' => '-'
    );

    // -- Remove duplicated spaces
    $stripped = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $string);

    // -- Returns the slug
    return strtolower(strtr($string, $table));


}
