<?php

namespace ALVARO\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;
use ALVARO\UserBundle\Entity\User;
use ALVARO\UserBundle\Form\UserType;

use Symfony\Component\HttpFoundation\Request;


class UserController extends Controller
{
 
 
 // ACCION INDEX DEL ARCHIVO ROUTING.YML
 
    public function indexAction(Request $request)
    {
        /* getDoctrine() es un metodo para acceder al doctrine, getManager trae los datos de la DB*/
        $em = $this ->getDoctrine() ->getManager();
     
        /* getRepository() es para hacer una cansulta al Bundel. findAll trae todos los registros de la DB*/
       //$users = $em->getRepository('ALVAROUserBundle:User')->findAll();
        
        /*
        $res = 'Lista de Usuarios : <br />';
        // genera un ciclo de nuestros usuarios
        foreach($users as $user)
        {
             // los metodos getUsername() y getEmail() los trae de User.php el nombre de Ususario y su email
            $res .= 'Usuario: ' . $user->getUsername(). ' - Email: ' . $user->getEmail() . '<br />';
        }
        // Response muestra en el navegador todos los registros
        return new Response($res);
        */
        
        $dql = "SELECT u FROM ALVAROUserBundle:User u ORDER BY u.id DESC";
        $users = $em->createQuery($dql);
        
        $paginator = $this->get('knp_paginator');
        // paginate( se pasa la sentencia dql, desde que pagina comienza, y total de registros por pagina)
        $pagination = $paginator->paginate(
            $users, $request->query->getInt('page', 1),
            4
            );
        // REnderisar hacia una vista ALVAROUserBundle:CARPETA:ARCHIVO.twig, enviar a un array los valores a la plantilla
        return $this->render('ALVAROUserBundle:User:index.html.twig', array('pagination' => $pagination));
        
    }
    
  // ACCION editar  DEL ARCHIVO ROUTING.YML    

    public function editAction($id)
    {
       $em = $this->getDoctrine()->getManager();
       // recuperamos el id del registro que queremos modificar
       $user = $em->getRepository('ALVAROUserBundle:User')->find($id); 
       
       //verificamos que el id existe
        if(!$user)
        {
            $messageException = $this->get('translator')->trans('User not found.');
            throw $this->createNotFoundException($messageException);
        }
        
        $form = $this->createEditForm($user);
        
       // Renderisamos a la vista, le enviamos los datos en un arreglo los datos del usuario y los datos del formulario
       
       return $this->render('ALVAROUserBundle:User:edit.html.twig', array('user' => $user, 'form' => $form->createView()));
    }
    
    // creamos la funcion privada y es similar a la de agregar 
    private function createEditForm(User $entity)
    {

        $form = $this->createForm(UserType::class, $entity, array('action' => $this->generateUrl('alvaro_user_update', array('id' => $entity->getId())), 'method' => 'PUT'));

        return $form;   
        
    }
    
    // con esta funcion actualizamos lo que enviamos del formulario update
    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $user = $em->getRepository('ALVAROUserBundle:User')->find($id);
        
        // si no encuentra el usuario
        if(!$user)
        {
            $messageException = $this->get('translator')->trans('User not found.');
            throw $this->createNotFoundException($messageException);
        }
        
        $form = $this->createEditForm($user);
        $form->handleRequest($request);
        
        // Verificamos si el formulario se envio correctamente y si son correctos los valores del formulario
        if($form->isSubmitted() && $form->isValid())
        {
            // recupero la variable password
            $password = $form->get('password')->getData();
            // Si se modifica la password
            if(!empty($password))
            {
               // la password pasada la encripta
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $password);
                $user->setPassword($encoded);
            }
            else
            {
                // si no modifica la password no la modifica
                $recoverPass = $this->recoverPass($id);
                $user->setPassword($recoverPass[0]['password']);                
            }
            
            //estamos recuperando el rol del registro que estamos modificando y si es ADMIN
            
            if($form->get('role')->getData() == 'ROLE_ADMIN')
            {
                // Si es administrador siempre sera el campo seteado en 1
                $user->setIsActive(1);
            }


         // este metodo guarda lo que hemos enviado con el formulario
            $em->flush();
            
           // mensaje de confirmacion, con translator.
            $successMessage = $this->get('translator')->trans('The user has been modified.');
            $this->addFlash('mensaje', $successMessage);
            
            return $this->redirectToRoute('alvaro_user_edit', array('id' => $user->getId()));
        }
        
        // si no se proceso el formulario, volvemos a enviar el form 
        return $this->render('ALVAROUserBundle:User:edit.html.twig', array('user' => $user, 'form' => $form->createView()));
    }
// Funcion para recuperar la clave de la DB    
    private function recoverPass($id)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT u.password
            FROM ALVAROUserBundle:User u
            WHERE u.id = :id'    
        )->setParameter('id', $id); // :id = que pasar $id con ->setParamenter
        
        $currentPass = $query->getResult();
        
        return $currentPass;
    }

  // ACCION VIEW DEL ARCHIVO ROUTING.YML
 
    public function viewAction($id)
    {
        /* se crea variable repositorio */
        $repository = $this->getDoctrine()->getRepository('ALVAROUserBundle:User');
        
        /* El metodo DOCTRINE nos trae varias formas de buscar resultados.*/
        
        // Con esto recuperamos el usuario que pasamos el ID.
        //$user = $repository->findOneById($id);
        //$user = $repository->findOneByUsername($nombre);
        $user = $repository->find($id);
        
        // si no encuentra el usuario, envia un mensaje

        if(!$user)
        {
            $messageException = $this->get('translator')->trans('User not found.');
            throw $this->createNotFoundException($messageException);
        }

        $deleteForm = $this->createCustomForm($user->getId(), 'DELETE', 'alvaro_user_delete');
//print_r(&deleteForm); exit;
        // Esto renderisa la vista, enviamos el usuario en un arreglo para que recupere lo que tiene que mostrar
        return $this->render('ALVAROUserBundle:User:view.html.twig', array('user' =>$user, 'delete_form' => $deleteForm->createView()));
    }

    private function createCustomForm($id, $method, $route)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($route, array('id' => $id)))
            ->setMethod($method)
            ->getForm();
    }

// FUNCION DE ELIMINAR REGISTRO

    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        // llamamos al getRepositorio que lo cargamos a una variable user, definimos el bundel utilizado y recuperamos con find el id
        $user = $em->getRepository('ALVAROUserBundle:User')->find($id);
        
         // si no encuentra el usuario, envia un mensaje       
        if(!$user)
        {
            $messageException = $this->get('translator')->trans('User not found.');
            throw $this->createNotFoundException($messageException);
        }
       
       // llamamos a nuestro formulario
       $form = $this->createCustomForm($user->getId(), 'DELETE', 'alvaro_user_delete');
       // procesamos e formulario
       $form->handleRequest($request);
       
       // si se ha enviado correctamente la peticion desde el forumlario y si es valido el formulario
        if($form->isSubmitted() && $form->isValid())
        {
            // eliminamos el registro enviando el usuario
            $em->remove($user);
            // ejecuta la sentencia
            $em->flush();
          // mensaje de confirmacion de eliminacion, con translator.
            $successMessage = $this->get('translator')->trans('The user has been deleted.');
            $this->addFlash('mensaje', $successMessage);
            // redirecciona a la pagina index
            return $this->redirectToRoute('alvaro_user_index');
        }    
       
       
    }    
    
// ACCION ADD DEL ARCHIVO ROUTING.YML
 
    public function addAction()
    {
        $user = new User();
        $form = $this->createCreateForm($user);
        
        // Esto renderisa e lformulario
        return $this->render('ALVAROUserBundle:User:add.html.twig', array('form' => $form->createView()));
        
    }
    
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(UserType::class, $entity, array(
                'action' => $this->generateUrl('alvaro_user_create'),
                'method' => 'POST'
            ));
        
        return $form;   
    }    
   
   public function createAction(Request $request)
   {
        $user = new User();
        $form = $this->createCreateForm($user);
        $form->handleRequest($request);
        
        if($form->isValid())
         {
                $password = $form-> get('password')->getData();
                
                // Se llama al constrein o constructor de NotBlank que no haya campo en blanco
                $passwordConstraint = new Assert\NotBlank();
                // validamos solo en enl campo password que no este en blanco
                $errorList = $this->get('validator')->validate($password, $passwordConstraint);

               // si no hay error que es igual a 0
               if(count($errorList) == 0)
                {             
                    // encrptamos el password
                    $encoder = $this->container->get('security.password_encoder');
                    $encoder = $encoder->encodePassword($user, $password);
                    
                    $user->setPassword($encoder);
                    
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    // graba los datos del formulario
                    $em->flush();
                    
                    //Envio un mensaje que el usuario a sido creado traducido
                    $successMessage = $this->get('translator')->trans('The user has been created.');
                    $this->addFlash('mensaje', $successMessage);
                    
                    return $this->redirectToRoute('alvaro_user_index');                      
                }
                else
                { // si hay algun error
                    $errorMessage = new FormError($errorList[0]->getMessage());
                    $form->get('password')->addError($errorMessage);
        
                }

         }        

        // Esto renderisa e lformulario
        return $this->render('ALVAROUserBundle:User:add.html.twig', array('form' => $form->createView()));
    
    }
       

    function saludoAction($name)
    {
                return $this->render('ALVAROUserBundle:Default:index.html.twig', array('name' => $name));
    }
    
}
