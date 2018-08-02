<?php

namespace Solunes\Payments\App\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

use Validator;
use Asset;
use AdminList;
use AdminItem;
use PDF;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class TestController extends Controller {

	protected $request;
	protected $url;

	public function __construct(UrlGenerator $url) {
	  $this->prev = $url->previous();
	  $this->module = 'test';
	}

    public function getEncryptionTest($texto = 'Texto de muestra') {
        $response = '<strong>Comenzando la prueba...</strong>';
        $encrypted = \Payments::encrypt($texto);
        $response .= '<br><br><strong>Texto Encriptado:</strong> '.$encrypted;
        $decrypted = \Payments::decrypt($encrypted);
        $response .= '<br><br><strong>Texto Decifrado:</strong> '.$decrypted;
        return $response;
    }

    public function getDecryptionTest($textoEncriptado) {
        $response = '<strong>Comenzando la prueba...</strong>';
        $decrypted = urldecode($textoEncriptado);
        $response .= '<br><br><strong>Texto Decodeado:</strong> '.$decrypted;
        $decrypted = \Payments::decrypt($decrypted);
        $response .= '<br><br><strong>Texto Decifrado:</strong> '.$decrypted;
        return $response;
    }

}