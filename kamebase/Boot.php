<?php
/**
 * Created by HAlex on 08/10/2017 20:43
 */

namespace kamebase;

class Boot {
    public static function matchRoutes(Request $request) {
        Request::setMainRequest($request);
        $content = Router::match($request);
        $response = null;

        if ($content instanceof Response) {
            return $content;
        } else {
            $response = new Response($request, $content);
        }

        if (is_null($content)) {
            $response->setStatusCode(404);
        }

        return $response;
    }
}