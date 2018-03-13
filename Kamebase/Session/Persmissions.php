<?php
/**
 * Created by HAlexTM on 09/03/2018 10:42
 */

namespace Kamebase\Session;


use Kamebase\Database\DB;
use Kamebase\Exceptions\LoadException;

class Persmissions {

    private $permissions = [];

    public function __construct(array $permissions) {
        $this->permissions = $permissions;
    }

    public function has(string $name) {
        if (isset($this->permissionse[$name])) {
            return $this->permissions[$name] > 0;
        }
        return false;
    }

    public function level(string $name, $default = 0) {
        if (isset($this->permissions[$name])) {
            return $this->permissions[$name];
        }
        return $default;
    }

    /**
     * @param int $user
     * @return array loaded permissions
     * @throws LoadException
     */
    public function load(int $user) {
        // TODO: Escape everything, make query classes
        $query = "SELECT `permission`, `level` FROM `permissions` WHERE user_id = $user";
        $result = DB::query($query);

        if ($result) {
            $this->permissions = [];

            while ($perm = $result->fetch_array(MYSQLI_NUM)) {
                $this->permissions[$perm[0]] = $perm[1];
            }

            return $this->permissions;
        }
        throw new LoadException("user permissions, user ID = " . $user);
    }
}