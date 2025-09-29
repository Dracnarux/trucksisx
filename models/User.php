
<?php
require_once __DIR__ . '/../config/db.php';

class User {
    private $db;
    private $table = "users";

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function login($usuario, $contrasena) {
        $sql = "SELECT * FROM " . $this->table . " WHERE (nombre = :usuario OR correo = :usuario)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            if (hash('sha256', $contrasena) === $user['contrasena']) {
                return $user;
            }
        }
        return false;
    }

    // Obtener usuario por número de documento (para validar conductores)
    public function getByDocumento($documento) {
        $sql = "SELECT * FROM " . $this->table . " WHERE num_documento = :documento";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':documento', $documento);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener todos los conductores
    public function getConductores() {
        $sql = "SELECT * FROM " . $this->table . " WHERE rol = 'conductor' ORDER BY nombre, apellido";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener usuario por ID
    public function getById($id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear nuevo usuario
    public function create($data) {
        $sql = "INSERT INTO " . $this->table . " 
                (num_documento, tipo_documento, nombre, apellido, num_celular, correo, rol, contrasena) 
                VALUES 
                (:num_documento, :tipo_documento, :nombre, :apellido, :num_celular, :correo, :rol, :contrasena)";
        
        $stmt = $this->db->prepare($sql);
        
        // Encriptar contraseña
        $hashedPassword = hash('sha256', $data['contrasena']);
        
        $stmt->bindParam(':num_documento', $data['num_documento']);
        $stmt->bindParam(':tipo_documento', $data['tipo_documento']);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':apellido', $data['apellido']);
        $stmt->bindParam(':num_celular', $data['num_celular']);
        $stmt->bindParam(':correo', $data['correo']);
        $stmt->bindParam(':rol', $data['rol']);
        $stmt->bindParam(':contrasena', $hashedPassword);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // Actualizar usuario
    public function update($id, $data) {
        $sql = "UPDATE " . $this->table . " SET 
                nombre = :nombre, 
                apellido = :apellido, 
                num_celular = :num_celular, 
                correo = :correo";
        
        // Solo actualizar contraseña si se proporciona
        if (!empty($data['contrasena'])) {
            $sql .= ", contrasena = :contrasena";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':apellido', $data['apellido']);
        $stmt->bindParam(':num_celular', $data['num_celular']);
        $stmt->bindParam(':correo', $data['correo']);
        $stmt->bindParam(':id', $id);
        
        if (!empty($data['contrasena'])) {
            $hashedPassword = hash('sha256', $data['contrasena']);
            $stmt->bindParam(':contrasena', $hashedPassword);
        }
        
        return $stmt->execute();
    }

    // Eliminar usuario
    public function delete($id) {
        $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    // Validar si un conductor existe y está activo
    public function validateConductor($documento) {
        $user = $this->getByDocumento($documento);
        return $user && $user['rol'] === 'conductor';
    }

    // Obtener todos los usuarios
    public function getAll() {
        $sql = "SELECT * FROM " . $this->table . " ORDER BY nombre, apellido";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
