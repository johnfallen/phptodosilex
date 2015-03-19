<?php
/**
 * I am the main Silex application file. I am the front controller.
 *
 * @author John Allen
 * @version 1.0
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/org/jfa/todo/com/Factory.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

$app = new Silex\Application();
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../app/views',
));

// hate this...
$app['todo.Factory'] = new org\jfa\todo\Factory();
// hate this...
$app['todo.controller'] = $app['todo.Factory']->getBean('Controller');

$app['debug'] = true;


// FRONT CONTROLLER

/* DEFAULT action */
$app->get('/', function (Request $request) use ($app) {

	$todos = $app['todo.controller']->listToDo();

	return $app['twig']->render('list.twig', array(
		'todos' => $todos
	));
});

/**
 * /deletecompleted
 */
$app->get('/deletecompleted', function (Request $request) use ($app) {
    
    $todos = $app['todo.controller']->deleteCompleted();

    $app['response'] = getResponse('Completed ToDos Were Cleared', 'info');

    // redirect to default action
    $subRequest = Request::create('/', 'GET');
    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

});

/**
 * /delete/{id}
 */
$app->get('/delete/{id}', function ($id, Request $request) use ($app) {
    
    $todos = $app['todo.controller']->deleteToDo( $id );

    $app['response'] = getResponse('The Todo Was Deleted', 'danger');

    // redirect to default action
    $subRequest = Request::create('/', 'GET');
    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

});

/**
 * /edit/{id}
 */
$app->get('/edit/{id}', function ($id, Request $request) use ($app) {
    
    $todo = $app['todo.controller']->getToDo( $id );
    
    return $app['twig']->render('edit.twig', array(
        'todo' => $todo
    ));
});

/**
 * /save
 */
$app->post('/save', function (Request $request) use ($app) {
    
	$data['id'] = $request->get('id');
	$data['task'] = $request->get('task');

    if ( !null == $request->get('complete') ){
		$data['complete'] = true;
    } else {
    	$data['complete'] = false;
	}

	$app['todo.controller']->saveToDo( $data );

	$app['response'] = getResponse('The Todo Was Saved', 'success');

	// redirect to '/''
	$subRequest = Request::create('/', 'GET');
	return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
});

$app->run();

/**
 * I return an associative array used by the front end to display messages.
 *
 * @param string $message  I am the message to display.  I default to an empty string.
 * @param string $type  I am the type of message.  I default to an empty string.
 * @return array
 */
function getResponse( $message = '', $type = 'info' ) {
	$result = array();
	$result['message'] = $message;
	$result['type'] = $type;
	return $result;
}