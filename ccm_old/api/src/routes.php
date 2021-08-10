<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/overview', function (Request $request, Response $response, array $args) {
    //$this->logger->addInfo("Ticket list");
    $items = new Items($this->db);
	
	$data = $items->getOverview();
	
    return $response->withJson($data);
});

$app->get('/item/next/{curr}/{org}/{mode}/{mode2}', function (Request $request, Response $response, array $args) {
    //$this->logger->addInfo("Ticket list");
    $items = new Items($this->db);
	
	$itemId = $items->getNextItem($args['org'],$args['curr'],$args['mode'],$args['mode2']);
	
    $itemData = $items->getIssueData($itemId,$args['org']);
	
    return $response->withJson($itemData);
});

$app->get('/item/{org}/{item}', function (Request $request, Response $response, array $args) {
    //$this->logger->addInfo("Ticket list");
    $items = new Items($this->db);
    $itemData = $items->getIssueData($args['item'],$args['org']);
	
    return $response->withJson($itemData);
});

$app->get('/organization/{org}', function (Request $request, Response $response, array $args) {
    //$this->logger->addInfo("Ticket list");
    $items = new Organizations($this->db);
    $itemData = $items->getOrganizationData($args['org']);
	
    return $response->withJson($itemData);
});

$app->post('/rating/{org}/{item}', function (Request $request, Response $response, array $args) {
    //$this->logger->addInfo("Ticket list");
	$params = $request->getParsedBody();//getQueryParams();//getParsedBody();
	//var_dump($params);
    $Ratings = new Ratings($this->db,$args['item'],$args['org']);
    $itemData = $Ratings->ratingUpdate($params);
	
    return $response->withJson($itemData);
});

$app->post('/save_organization', function (Request $request, Response $response, array $args) {
    //$this->logger->addInfo("Ticket list");
	$params = $request->getParsedBody();//getQueryParams();//getParsedBody();
	//var_dump($params);
    $Ratings = new Organizations($this->db);
    $itemData = $Ratings->saveOrg($params);
	
    return $response->withJson($itemData);
});

$app->post('/save_item', function (Request $request, Response $response, array $args) {
    //$this->logger->addInfo("Ticket list");
	$params = $request->getParsedBody();//getQueryParams();//getParsedBody();
	//var_dump($params);
    $Ratings = new Items($this->db);
    $itemData = $Ratings->saveItem($params);
	
    return $response->withJson($itemData);
});