<?php
class dbManager
{
    public static function Connect($dbname)
    {
        $dsn = "mysql:dbname={$dbname};host=192.168.2.200";
        try
        {
            $pdo = new PDO($dsn, 'joshua_becchiati', 'undeserved.Mafiosos.brayed.');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $exception)
        {
            die("Connection to database failed: " . $exception->getMessage());
        }
    }
}