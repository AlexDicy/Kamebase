<?php
/**
 * Created by HAlexTM on 11/03/2018 17:49
 */

namespace Kamebase\Database;


use Kamebase\Database\Schema\Schema;

class DefaultTables {
    public function getTables() {
        $tables = [];

        $table = Schema::create("users");
        $table->column("id")->autoIncrement()->primaryKey();
        $table->column("username", "varchar", 40);
        $table->column("name", "varchar", 40);
        $table->column("lastname", "varchar", 40);
        $table->column("email", "varchar", 40);
        $table->column("password", "varchar", 40);
        $tables[] = $table;

        // Table structure for Database based sessions
        $session = Schema::create("sessions");
        $session->column("id", "varchar", 64)->primaryKey();
        $session->column("ip", "varchar", 40);
        $session->column("last_activity", "timestamp");
        $session->column("user_agent", "text");
        $session->column("data", "text");
        //$tables[] = $session;
    }
}