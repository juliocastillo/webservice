<?php
namespace Minsal\RestBundle\Service;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Minsal\RestBundle\Entity\Persona as Persona;


class PeopleAPIService {
    private $container = null;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
    public function API($id = null){
        header('Content-Type: application/JSON');
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
        case 'GET'://consulta
            echo $this->getPeoples($id);
            break;
        case 'POST'://inserta
            echo $this->savePeople();
            break;
        case 'PUT'://actualiza
            echo $this->modifyPeople();
            break;
        case 'DELETE'://elimina
            echo 'DELETE';
            break;
        default://metodo NO soportado
            echo 'METODO NO SOPORTADO';
            break;
        }
    }

    /**
     * obtiene todos los registros de la tabla "people"
     * @return Array array con los registros obtenidos de la base de datos
     */
    public function getPeoples($id = null){
        $em = $this->container->get('doctrine')->getManager();
        if ($id) {
            $people = $em->getRepository('MinsalRestBundle:Persona')->find($id);
            if ($people) {
                return $this->serializar_obj_json($people);
            }
        } else {
            $peoples = $em->getRepository('MinsalRestBundle:Persona')->findAll();
            if ($peoples) {
                return $this->serializar_obj_json($peoples);
            }
        }
        //204 : No Content → La petición se ha completado con éxito pero su respuesta no tiene ningún contenido
        return $this->response(422, "error", "No content");
    }

    /**
     * obtiene todos los registros de la tabla "people"
     * @return Array array con los registros obtenidos de la base de datos
     */
    public function savePeople(){
        $obj = json_decode( file_get_contents('php://input') );
         if (empty((array) $obj)){
             $this->response(422,"error","No se añadieron datos, check json");
         } else if(isset($obj->nombre) && isset($obj->tipoSangre)) {
             $em = $this->container->get('doctrine')->getManager();
             $people = new Persona();
             $people->setNombre($obj->nombre);
             $people->setTipoSangre($obj->tipoSangre);
             $em->persist($people);
             $em->flush();
             $this->response(200,"success","un nuevo registro fue añadido ".$people->getId());
         } else {
             $this->response(422,"error","La propiedad no está definida");
         }
        return;
    }

    /**
     * obtiene todos los registros de la tabla "people"
     * @return Array array con los registros obtenidos de la base de datos
     */
    public function modifyPeople(){
        $obj = json_decode( file_get_contents('php://input') );
         if (empty((array) $obj)){
             $this->response(422,"error","No se modificaron datos, check json");
         } else if(isset($obj->id) && isset($obj->nombre) && isset($obj->tipoSangre)) {
             $em = $this->container->get('doctrine')->getManager();
             $people = $em->getRepository('MinsalRestBundle:Persona')->find($obj->id);
             if ($people) {
                 $people->setNombre($obj->nombre);
                 $people->setTipoSangre($obj->tipoSangre);
                 $em->persist($people);
                 $em->flush();
                 $this->response(200,"success","un registro fue modificado ".$people->getId());
            }
         } else {
             $this->response(422,"error","La propiedad no está definida");
         }
        return;
    }


    /**
     * Respuesta al cliente
     * @param int $code Codigo de respuesta HTTP
     * @param String $status indica el estado de la respuesta puede ser "success" o "error"
     * @param String $message Descripcion de lo ocurrido
     */
     function response($code=200, $status="", $message="") {
        http_response_code($code);
        if( !empty($status) && !empty($message) ){
            $response = array("status" => $status ,"message"=>$message);
            echo json_encode($response,JSON_PRETTY_PRINT);
        }
     }

    public function serializar_obj_json ($obj){
        $encoders    = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer  = new Serializer($normalizers, $encoders);
        $jsoncontent = $serializer->serialize($obj, 'json');
        return $jsoncontent;
    }

}//end class
