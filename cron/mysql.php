<?php 
/**
 * Existen 3 maneras de trabajar PHP con MySQL
 * Orientada a Objetos (OB) 
 * Procedimiento (P)
 * PDO
 */

 include "../config.php";

 class MySQL {
    private $oConnBD = null;

    public function __construct(){
        global $usuarioBD, $passBD, $ipBD;

        $this->usuarioBD = $usuarioBD;
        $this->passBD = $passBD;
        $this->ipBD = $ipBD;
    }

    /**
     * Conexion BD por objetos
     */
    public function conBDOB(){
        $this->oConnBD = new mysqli($this->ipBD, $this->usuarioBD, $this->passBD);
        if($this->oConnBD->connect_error){
            echo "Error al conectar a la base de datos: " . $this->oConBD->connect_error . "\n";
            return false;
        }
        echo "Conexión exitosa..."."\n";
        return true;
    }

    /**
     * Conexión por Procedimientos
     */
    public function conBDP(){
        $this->oConnBD = mysqli_connect($this->ipBD, $this->usuarioBD, $this->passBD);
        if(!$this->oConnBD){
            echo "Error al conectar a la base de datos: " . mysqli_connect_error() . "\n";
            return false;
        }
        echo "Conexión exitosa..."."\n";
        return true;
    }

    /**
     * Conexion a traves de PDO
     */
    public function conBDPDO(){
        try {
            $this->oConnBD = new PDO("mysql:host=" . $this->ipBD, $this->usuarioBD, $this->passBD);
            echo "Conexión exitosa..."."\n";
            return true;
        } catch(PDOException $e) {
            echo "Error al conectar a la base de datos: " . $e->getMessage() . "\n";
            return false;
        }
    } 
 }
