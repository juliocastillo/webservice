<?php

namespace Minsal\RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class DefaultController extends Controller
{
    /**
     * @Route("/peoples/{id}")
     */
    public function peoplesAction(Request $request, $id = null)
    {
        $request = Request::createFromGlobals();
        $peopleAPI = $this->container->get('PeopleAPI.service');
        if (!$id) { //obteniendo el ID a traves de variables globales
            $id = $request->query->get('id');
        }
        return new Response ($peopleAPI->API($id));
    }

    /**
     * @Route("/alter/peoples")
     */
    public function alterPeoples()
    {
        $url = "http://webservice.localhost/app_dev.php/peoples";
        $data_string = '{"id":"5","nombre":"Maria", "tipoSangre":"O RH-"}';
        $ch = curl_init($url);
        curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          'Content-Type: application/json',
          'Content-Length: ' . strlen($data_string))
        );
        $response = curl_exec($ch);
        return new Response ($response);
    }
}
