<?php
/**
 * Created by HAlex on 08/10/2017 20:43
 */

namespace Kamebase;

use Kamebase\Exceptions\ResponseException;
use Kamebase\Router\Router;
use Reply;

class Boot {
    private static $allowedHeaders = "Access-Control-Allow-Headers, Origin, Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers";

    public static function matchRoutes(Request $request) {
        if ($request->getMethod() === "OPTIONS") {
            $response = new Response($request);
            $response->headers["Access-Control-Allow-Origin"] = "*";
            $response->headers["Access-Control-Allow-Methods"] = implode(",", Router::getAvailableMethods($request));
            $response->headers["Access-Control-Allow-Headers"] = self::$allowedHeaders;
            return $response;
        }
        try {
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
        } catch (ResponseException $e) {
            $reply = new Reply(null, false, $e->getMessage(), [$e->getMessage()]);
            return new Response($request, $reply);
        }

        return $response;
    }
}