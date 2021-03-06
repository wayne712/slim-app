<?php
// DIC configuration
$container = $app->getContainer();
// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    $view = new \Slim\Views\Twig($settings['template_path'], [
        'cache' => $settings['cache_path'],
        'debug' => true,
    ]);
    $uri = $c['request']->getUri();
    $view->addExtension(new \Slim\Views\TwigExtension(
        $c['router'],
        $uri
    ));
    $view->addExtension(new Twig_Extension_Debug());
    $view->offsetSet('user', $c['user']);
    $view->offsetSet('staticPath', $uri->getBaseUrl() . '/static/');

    return $view;
};
// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};

// my db
$container['db'] = function($c) {
    $settings = $c->get('settings')['db_config'];

    return \App\lib\MyDb::getInstance($settings['dsn'], $settings['user'], $settings['password'], $settings['debug']);
};

// model
$container['model'] = function($c) {
    return new \App\lib\Loader($c);
};
$container['actual_model'] = $container->factory(function($c) {

    $modelName = $c->get('model_name');
    if ($modelName) {

        $modelFullName = 'App\\models\\' . $modelName;
        return new $modelFullName($c);
    }
    return false;
});

// pagination
$container['pagination'] = function($c) {
    $settings = $c->get('settings')['pagination'];
    return new \App\lib\Pagination($settings['per_nums'], $settings['page_display_nums']);
};

// cookie
$container['cookie'] = function ($c) {

    $request = $c->get('request');
    $parseCookies = \Slim\Http\Cookies::parseHeader($request->getHeader('cookie'));
    $cookie = new \Slim\Http\Cookies($parseCookies);

    return $cookie;
};

// session
$container['session'] = function ($c) {
    session_start();
    return new \App\lib\Session();
};

// flash
$container['flash'] = function ($c) {
    return new \App\lib\Flash($c['session']);
};

// user
$container['user'] = function ($c) {

    $user = ['id' => 0, 'name' => 'guest'];
    $session = $c['session'];
    if (isset($session['user'])) {
        $user = $session['user'];
    }
    return $user;
};

// current route name
$container['current_path'] = function ($c) {
    return $c->get('request')->getUri()->getPath();
};