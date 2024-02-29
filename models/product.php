<?php
require '../connection/dbmanager.php';
class product
{
    private $id;
    private $nome;
    private $prezzo;
    private $marca;

    public function getId()
    {
        return $this->id;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function getPrezzo()
    {
        return $this->prezzo;
    }

    public function setPrezzo($prezzo)
    {
        $this->prezzo = $prezzo;
    }

    public function getMarca()
    {
        return $this->marca;
    }

    public function setMarca($marca)
    {
        $this->marca = $marca;
    }

    public static function Find($id)
    {
        $pdo = self::Connect();
        $stmt = $pdo->prepare("select * from joshua_becchiati_ecommerce.products where id = :id");
        $stmt->bindParam(":id", $id);
        if ($stmt->execute()) {
            return $stmt->fetchObject("Product");
        } else {
            return false;
        }
    }

    public static function Create($params)
    {
        $pdo = self::Connect();
        $stmt = $pdo->prepare("insert into joshua_becchiati_ecommerce.products (nome,prezzo,marca) values (:nome,:prezzo,:marca)");
        $stmt->bindParam(":nome", $params["nome"]);
        $stmt->bindParam(":prezzo", $params["prezzo"]);
        $stmt->bindParam(":marca", $params["marca"]);
        if ($stmt->execute()) {
            $stmt = $pdo->prepare("select * from joshua_becchiati_ecommerce.products order by id desc limit 1");
            $stmt->execute();
            return $stmt->fetchObject("Product");
        } else {
            throw new PDOException("Error during creation");
        }
    }

    public function Delete()
    {
        if(!$this->getId())
        {
            return false;
        }
        $id = $this->getId();
        $pdo = self::Connect();
        $stmt = $pdo->prepare("delete from  joshua_becchiati_ecommerce.products where id = :id");
        $stmt->bindParam(':id',$id,PDO::PARAM_INT);
        $stmt->execute();
        return true;
    }

    public static function FetchAll()
    {
        $pdo = self::Connect();
        $stmt = $pdo->query("select * from joshua_becchiati_ecommerce.products");
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Product');

    }

    public function Update($params)
    {
        $pdo = self::Connect();
        $stmt = $pdo->prepare("UPDATE joshua_becchiati_ecommerce.products SET nome = :nome, prezzo = :prezzo, marca = :marca WHERE id = :id");
        $stmt->bindParam(":nome", $params['nome']);
        $stmt->bindParam(":prezzo", $params['prezzo']);
        $stmt->bindParam(":marca", $params['marca']);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public static function Connect()
    {
        return dbManager::Connect("joshua_becchiati_ecommerce");
    }


}