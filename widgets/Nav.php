<?php

namespace app\widgets;

use Yii;
use yii\bootstrap\Nav as BootstrapNav;

class Nav extends BootstrapNav
{
    protected function isItemActive($item)
    {
        $active = parent::isItemActive($item);

        if (isset($item['activeController']) && $item['activeController'] && !$active && isset($item['url']) && is_array($item['url']) && isset($item['url'][0])) {
            $controllerName =  Yii::$app->controller->getUniqueId();
            $route = $item['url'][0];
            if ($route[0] !== '/' && Yii::$app->controller) {
                $route = Yii::$app->controller->module->getUniqueId() . '/' . $route;
            }

            if (strpos(ltrim($route, '/'), $controllerName) === 0) {
                return true;
            }
        }

        if (isset($item['activeRoutes']) && $item['activeRoutes'] && !$active && isset($item['url']) && is_array($item['url']) && isset($item['url'][0])) {
            $requestesRoute = Yii::$app->requestedRoute;
            if (is_array($item['activeRoutes'])) {
                foreach ($item['activeRoutes'] as $route) {
                    if ($route === $requestesRoute) {
                        return true;
                    }
                }
            } else if ($item['activeRoutes'] === $requestesRoute) {
                return true;
            }
        }

        return $active;
    }
}