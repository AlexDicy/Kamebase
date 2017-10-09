<?php
/**
 * Created by HAlex on 08/10/2017 20:43
 */

namespace kamebase;

class Boot {
    public static function matchRoutes(Request $request) {
        $route = Router::match($request);
        $request->setRoute($route);
    }
}