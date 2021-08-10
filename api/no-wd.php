<?php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

class DB
{
    public $pdo;

    public function __construct($db, $username = null, $password = null, $host = '127.0.0.1', $port = 5432, $options = [])
    {
        $default_options = [
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ];
        $options = array_replace($default_options, $options);
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

        try {
            $this->pdo = new \PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    public function run($sql, $args = null)
    {
        if (!$args) {
            return $this->pdo->query($sql);
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    }
}

$passwordfile = '/data/project/edgars/replica.my.cnf' ;
$config = parse_ini_file( $passwordfile );
if ( isset( $config['user'] ) ) {
	$mysql_user = $config['user'];
}
if ( isset( $config['password'] ) ) {
	$mysql_password = $config['password'];
}

$sourceConn = new DB("lvwiki_p", $mysql_user, $mysql_password, "lvwiki.web.db.svc.wikimedia.cloud");

$pageTitles = $sourceConn->run("SELECT p.page_title
from page p
where p.page_namespace=0
and p.page_title not like \"%/%\"
and p.page_is_redirect=0
and p.page_id not in (SELECT  eu_page_id
				from wbc_entity_usage wb
				GROUP BY eu_page_id)
and p.page_id not in (select ll_from from langlinks ll )
and p.page_id not in (select cl_from
						from categorylinks
						where cl_to in (\"Dzēšanai_izvirzītās_lapas\",\"Nozīmju_atdalīšana\"))
and p.page_id not in (select t.tl_from from templatelinks t
				  where t.tl_namespace = 10 AND t.tl_title='Uzvārds')
order by p.page_id desc")->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($pageTitles);
