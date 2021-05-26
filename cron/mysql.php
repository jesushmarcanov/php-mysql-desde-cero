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

    public $sqlCDB = "CREATE DATABASE db_php_mysql";

    public $sqlTabla = "
        CREATE TABLE resumen_productos (
            id_resumen                  INT(11)     UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre                      VARCHAR(45) NOT NULL,
            categoria                   VARCHAR(45) NOT NULL,
            precio                      FLOAT       NOT NULL,
            cantidad_vendidos           INT(11)     NOT NULL,
            en_almacen                  INT(11)     NOT NULL,
            fecha_alta                  datetime    NOT NULL
        )
    ";
    
    //Metodo insert normal
    public $strInsert_old = "
		insert into resumen_productos
			(nombre,categoria,precio,cantidad_vendidos,en_almacen,fecha_alta)
		values
			('producto-1','categoria-2', 199.00, 30, 100,'2019-01-01')
        ";

    //Metodo insert con sentencias preparadas    
    public $strInsert = "
		insert into resumen_productos
			(nombre,categoria,precio,cantidad_vendidos,en_almacen,fecha_alta)
		values
			(?, ?, ?, ?, ?, ?)
        ";

        //Metodo Select para Objetos y Procedimientos
        private $strSelect = "
        select
            id_resumen, nombre, categoria, precio, cantidad_vendidos, en_almacen, fecha_alta
        from resumen_productos
        where
            cantidad_vendidos > ?
        order by precio desc
        limit ?
        ;
    ";

    //Metodo select para PDO
    private $strSelectPDO = "
        select
            id_resumen, nombre, categoria, precio, cantidad_vendidos, en_almacen, fecha_alta
        from resumen_productos
        where
            cantidad_vendidos > :cantidad_vendidos
        order by precio desc
        limit :limit
        ;
    "; 
    
    //Metodo update para objetos y procedimientos
    private $strUpdate = "
        update resumen_productos
        set
             nombre = ?
            ,categoria = ?
        where
            id_resumen = ?
    ";

    //Metodo update para PDO
    private $strUpdatePDO = "
        update resumen_productos
        set
             nombre = :nombre
            ,categoria = :categoria
        where
            id_resumen = :id_resumen
    ";

    //MEtodo Delete Objetos y Procedimientos
    private $strDelete = "
        delete from resumen_productos where id_resumen = ?
    ";

    //MEtodo Delete PDO
    private $strDeletePDO = "
        delete from resumen_productos where id_resumen = :id_resumen
    ";

    public function __construct(){
        global $usuarioBD, $passBD, $ipBD, $nombreBD;

        $this->usuarioBD = $usuarioBD;
        $this->passBD = $passBD;
        $this->ipBD = $ipBD;
        $this->nombreBD = $nombreBD;
    }

    /**
     * Conexion BD por objetos
     */
    public function conBDOB(){
        $this->oConnBD = new mysqli($this->ipBD, $this->usuarioBD, $this->passBD, $this->nombreBD);
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
        $this->oConnBD = mysqli_connect($this->ipBD, $this->usuarioBD, $this->passBD, $this->nombreBD);
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
            $this->oConnBD = new PDO("mysql:host=" . $this->ipBD . ";dbname=" . $this->nombreBD, $this->usuarioBD, $this->passBD);
            echo "Conexión exitosa..."."\n";
            return true;
        } catch(PDOException $e) {
            echo "Error al conectar a la base de datos: " . $e->getMessage() . "\n";
            return false;
        }
    } 

    /**
     * Ejecuta un Query con la sintaxis Objetos
     */
    public function execStrQueryOB($query){
        $id;
        if($this->conBDOB() && $query != ''){
            if($this->oConnBD->query($query) === true){
                $id = $this->oConnBD->insert_id;
                echo "Consulta ejecutada \n id " . $id . "\n";
            }else {
                echo "Error al ejecutar consulta " . $this->oConnBD->error . "\n";
            }
            $this->oConnBD->close();
        }
    }
    
    /**
     * Ejecuta un Query con la sintaxis por Procedimiento
     */
    public function execStrQueryP($query){
        $id;
        if($this->conBDP() && $query != ''){
            if(mysqli_query($this->oConnBD, $query)){
                $id = $this->oConnBD->insert_id;
                echo "Consulta ejecutada \n id " . $id . "\n";
            }else {
                echo "Error al ejecutar consulta " . mysqli_error($this->oConnBD) . "\n";
            }
            mysqli_close($this->oConnBD);
        }
        return $id;
    }

    /**
     * Ejecuta un Query con la sintaxis PDO
     */
    public function execStrQueryPDO($query){
        try {
            $id;
            if($this->conBDPDO() && $query != ''){
                $this->oConnBD->exec($query);
                $id = $this->oConnBD->lastInsertId();
                echo "Consulta ejecutada \n id " . $id . "\n";
                return $id;
            }
        } catch (PDOException $e) {
            echo "MySQL.execStrQueryPDO -- Error -- " . $e->getMessage() . "\n";
        }
    }

    /**
     * Sintaxis Objetos
     * file_get_contents - permite leer un archivo json
     * json_decode: Convierte un string en un JSON 
     * ssdiis
     */
    public function insertarOB(){
        $json = file_get_contents('./datos.json');
        $jsonDatos = json_decode($json, true);
        //print_r($jsonDatos);
        if($this->conBDOB()){
            //echo ($this->strInsert);
            //Disminuye el riesgo de inyecciones SQL
            $pQuery = $this->oConnBD->prepare($this->strInsert);
            foreach ($jsonDatos as $id => $valor) {
                $pQuery->bind_param(
                    "ssdiis",
                    $valor["nombre"],
                    $valor["categoria"],
                    $valor["precio"],
                    $valor["cantidad_vendidos"],
                    $valor["en_almacen"],
                    $valor["fecha_alta"]
                );
                $pQuery->execute();
                //Comprobando insert recibiendo el ultimo ID
                $idInsertado = $this->oConnBD->insert_id;
                echo ("Nombre:" . $valor["nombre"] . ", Ultimo ID: " .  $idInsertado . "\n");
            }
            $pQuery->close();
            $this->oConnBD->close();
        }
    }

     /**
     * Sintaxis Procedimientos
     */
    public function insertarP(){
        $json = file_get_contents('./datos.json');
        $jsonDatos = json_decode($json, true); 
        if ($this->conBDP()) {
            $pQuery = mysqli_stmt_init($this->oConnBD);
            mysqli_stmt_prepare($pQuery, $this->strInsert);
            foreach ($jsonDatos as $id => $valor) {
                mysqli_stmt_bind_param(
                    $pQuery, 
                    "ssdiis",
                    $valor["nombre"],
                    $valor["categoria"],
                    $valor["precio"],
                    $valor["cantidad_vendidos"],
                    $valor["en_almacen"],
                    $valor["fecha_alta"]
                );
                mysqli_stmt_execute($pQuery);
                $idInsertado = $this->oConnBD->insert_id;
                echo ("Nombre:" . $valor["nombre"] . ", Ultimo ID: " .  $idInsertado . "\n");
            }
            mysqli_close($this->oConnBD);
        }
    }

     /**
     * Sintaxis PDO
     */
    public function insertarPDO(){
        $json = file_get_contents('./datos.json');
        $jsonDatos = json_decode($json, true);
        try {
            $this->strInsert = "
		        insert into resumen_productos
			        (nombre,categoria,precio,cantidad_vendidos,en_almacen,fecha_alta)
		        values
			        (:nombre,
                     :categoria,
                     :precio,
                     :cantidad_vendidos,
                     :en_almacen,
                     :fecha_alta)
                 ";
                 if ($this->conBDPDO()) {
                     $pQuery = $this->oConnBD->prepare($this->strInsert);
                     foreach ($jsonDatos as $id => $valor) {
                         $pQuery->bindParam(':nombre', $valor["nombre"]);
                         $pQuery->bindParam(':categoria', $valor["categoria"]);
                         $pQuery->bindParam(':precio', $valor["precio"]);
                         $pQuery->bindParam(':cantidad_vendidos', $valor["cantidad_vendidos"]);
                         $pQuery->bindParam(':en_almacen', $valor["en_almacen"]);
                         $pQuery->bindParam(':fecha_alta', $valor["fecha_alta"]);
                         $pQuery->execute();
                         $idInsertado = $this->oConnBD->lastInsertId();
                         echo ("Nombre:" . $valor["nombre"] . ", Ultimo ID: " .  $idInsertado . "\n");
                     }
                     $this->oConnBD = null;
                 }
        } catch (PDOException $e) {
            echo ("MysSQL.insertarPDO -- " . $e->getMessage() . "\n");
        }
        
    }

    /**
     * Sintaxis Objetos 
     * Selecciona un limite de datos segun el criterio
     */
    public function consultarOB() {
        $cantidad = 50;
        $noProductos = 2;
        if($this->conBDOB()){
            $pQuery = $pQuery = $this->oConnBD->prepare($this->strSelect);
            $pQuery->bind_param("ii", $cantidad, $noProductos);
            $pQuery->execute();
            $productos = $pQuery->get_result();
            while ($producto = $productos->fetch_assoc()) {
                printf("id: %s, nombre: %s, categoria: %s, precio %s, vendidos: %s, en almacen: %s, fecha: %s \n",
                $producto["id_resumen"],
                $producto["nombre"],
                $producto["categoria"],
                $producto["precio"],
                $producto["cantidad_vendidos"],
                $producto["en_almacen"],
                $producto["fecha_alta"]
                );
            }
            $pQuery->close();
            $this->oConnBD->close();
        }
    }

    /**
     * Sintaxis Objetos 
     *Update
     */
    public function consultarOBU() {
        $id  = 1;
        $nombreP = "Producto Modificado OB";
        $catP = "Categoría Ewebik OB";

        if($this->conBDOB()){
            $pQuery = $this->oConBD->prepare($this->strUpdate);
            $pQuery->bind_param("ssi", $nombreP, $catP, $id);
            $pQuery->execute();
            $pQuery->close();
            $this->oConnBD->close();
        }
    }

    /**
     * Sintaxis Objetos 
     * Delete
     */
    public function consultarOBD() {
        $id  = 1;

        if($this->conBDOB()){
            $pQuery = $this->oConnBD->prepare($this->strDelete);
            $pQuery->bind_param("i", $id);
            $pQuery->execute();
            $pQuery->close();
            $this->oConnBD->close();
        }
    }

    

    /**
     * Sintaxis Procedimientos
     * Selecciona un limite de datos segun el criterio
     */
    public function consultarP() {
        $cantidad = 50;
        $noProductos = 100;
        if($this->conBDP()){
            $pQuery = mysqli_stmt_init($this->oConnBD);
            mysqli_stmt_prepare($pQuery, $this->strSelect);
            mysqli_stmt_bind_param($pQuery, "ii", $cantidad, $noProductos);
            mysqli_stmt_execute($pQuery);
            mysqli_stmt_bind_result($pQuery, $id_resumen, $nombre, $categoria, $precio, $cantidad_vendidos, $en_almacen, $fecha_alta);
            while (mysqli_stmt_fetch($pQuery)) {
                printf("id: %s, nombre: %s, categoria: %s, precio %s, vendidos: %s, en almacen: %s, fecha: %s \n", 
                    $id_resumen, 
                    $nombre,
                    $categoria, 
                    $precio, 
                    $cantidad_vendidos, 
                    $en_almacen, 
                    $fecha_alta
                );
            }
            mysqli_stmt_close($pQuery);
            mysqli_close($this->oConnBD);
        }
    }

     /**
     * Sintaxis Procedimientos
     * Update
     */
    public function consultarPU() {
        $id  = 1;
        $nombreP = "Producto Modificado P";
        $catP = "Categoría Ewebik P";
        if($this->conBDP()){
            $pQuery = mysqli_stmt_init($this->oConnBD);
            mysqli_stmt_prepare($pQuery, $this->strUpdate);
            mysqli_stmt_bind_param($pQuery, "ssi", $nombreP, $catP, $id);
            mysqli_stmt_execute($pQuery);
            mysqli_stmt_close($pQuery);
            mysqli_close($this->oConnBD);
        }
    }

    /**
     * Sintaxis Procedimientos
     * Delete
     */
    public function consultarPD() {
        $id  = 2;
        if($this->conBDP()){
            $pQuery = mysqli_stmt_init($this->oConnBD);
            mysqli_stmt_prepare($pQuery, $this->strDelete);
            mysqli_stmt_bind_param($pQuery, "i", $id);
            mysqli_stmt_execute($pQuery);
            mysqli_stmt_close($pQuery);
            mysqli_close($this->oConnBD);
        }
    }

    /**
     * Sintaxis PDO
     * Selecciona un limite de datos segun el criterio
     */
    public function consultarPDO() {
        $cantidad = 50;
        $noProductos = 100;
        try {
            if ($this->conBDPDO()) {
                $pQuery = $this->oConnBD->prepare($this->strSelectPDO);
                $pQuery->bindValue(':cantidad_vendidos', $cantidad, PDO::PARAM_INT);
                $pQuery->bindValue(':limit', $noProductos, PDO::PARAM_INT);
                $pQuery->execute();
                $pQuery->setFetchMode(PDO::FETCH_ASSOC);
                while ($producto = $pQuery->fetch()) {
                    printf("id: %s, nombre: %s, categoria: %s, precio %s, vendidos: %s, en almacen: %s, fecha: %s \n",
                        $producto["id_resumen"],
                        $producto["nombre"],
                        $producto["categoria"],
                        $producto["precio"],
                        $producto["cantidad_vendidos"],
                        $producto["en_almacen"],
                        $producto["fecha_alta"]
                    );
                } 
                $this->oConnBD = null;
            }
        } catch (PDOException $e) {
            echo ("MysSQL.consultarPDO -- " . $e->getMessage() . "\n");
        }
    }

    /**
     * Sintaxis PDO
     * Update
     */
    public function consultarPDOU() {
        $id  = 1;
        $nombreP = "Producto Modificado PDO";
        $catP = "Categoría Ewebik PDO";
        try {
            if ($this->conBDPDO()) {
                $pQuery = $this->oConnBD->prepare($this->strUpdatePDO);
                $pQuery->bindValue(':nombre', $nombreP, PDO::PARAM_STR);
                $pQuery->bindValue(':categoria', $catP, PDO::PARAM_STR);
                $pQuery->bindValue(':id_resumen', $id, PDO::PARAM_INT);
                $pQuery->execute();
                $this->oConnBD = null;
            }
        } catch (PDOException $e) {
            echo ("MysSQL.consultarPDOU -- " . $e->getMessage() . "\n");
        }
    }

    /**
     * Sintaxis PDO
     * Delete
     */
    public function consultarPDOD() {
        $id  = 3;
        try {
            if ($this->conBDPDO()) {
                $pQuery = $this->oConnBD->prepare($this->strDeletePDO);
                $pQuery->bindValue(':id_resumen', $id, PDO::PARAM_INT);
                $pQuery->execute();
                $this->oConnBD = null;
            }
        } catch (PDOException $e) {
            echo ("MysSQL.consultarPDOD -- " . $e->getMessage() . "\n");
        }
    }
 }
