<?php



class Database {



    private $db_host;

    private $db_user;

    private $db_pass;

    private $db_name;



    public function connect() {

        /* Put your database info here */

        $this->db_host = 'X';

        $this->db_user = 'X';

        $this->db_pass = 'X';

        $this->db_name = 'X';

        try {

            $dsn = "mysql:host={$this->db_host};dbname={$this->db_name}";

            $pdo = new PDO($dsn, $this->db_user, $this->db_pass);

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;

        } catch (PDOException $e) {

            die("Connection failed: " . $e->getMessage());

        }

    }

}



require_once('classes/Article.php');

require_once('classes/Category.php');

require_once('classes/Setting.php');

require_once('classes/User.php');





?>