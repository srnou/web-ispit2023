<?php

class MidtermDao {

    private $conn;

    /**
    * constructor of dao class
    */
    public function __construct(){
        try {

        /** TODO
        * List parameters such as servername, username, password, schema. Make sure to use appropriate port
        */

        $host = 'localhost';
        $username = 'root';
        $password = 'rootpw';
        $dbname = 'midterm2-3';
        $port = 3306;
        
      /** TODO
        * Create new connection
        * Use $options array as last parameter to new PDO call after the password
    
      */

      $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];

         $this->conn = new PDO(
          "mysql:host=" . $host . ";dbname=" . $dbname . ";port=" . $port,
          $username, $password, $options,

      );

        // set the PDO error mode to exception
      
        } catch(PDOException $e) {
          echo "Connection failed: " . $e->getMessage();
        }
    }

    

    /** TODO
    * Implement DAO method used to get cap table
    */
    public function cap_table(){

      $query = "
      SELECT 
                sc.description AS class,
                scc.description AS category,
                CONCAT(i.first_name, ' ', i.last_name) AS investor,
                ct.diluted_shares
            FROM 
                share_classes sc
            JOIN 
                share_class_categories scc ON sc.id = scc.share_class_id
            JOIN 
                cap_table ct ON scc.id = ct.share_class_category_id
            JOIN 
                investors i ON ct.investor_id = i.id
            ORDER BY 
                sc.description, scc.description, i.first_name;
  ";

      $stmt = $this->conn->prepare($query);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** TODO
    * Implement DAO method used to add cap table record
    */
    public function add_cap_table_record($data){

      $query = "INSERT INTO cap_table (investor_id, share_class_category_id, share_class_id, diluted_shares)
                  VALUES (:investor_id, :share_class_category_id, :share_class_id, :diluted_shares)";
       
       $stmt = $this->conn->prepare($query);
      
       $stmt->bindParam(':investor_id', $data['investor_id'], PDO::PARAM_INT);
       $stmt->bindParam(':share_class_category_id', $data['share_class_category_id'], PDO::PARAM_INT);
       $stmt->bindParam(':share_class_id', $data['share_class_id'], PDO::PARAM_INT);
       $stmt->bindParam(':diluted_shares', $data['diluted_shares'], PDO::PARAM_STR);
     
       $stmt->execute();
       $newId = $this->conn->lastInsertId();

       return [
        'diluted_shares' => $data['diluted_shares'],
        'id' => $newId,
        'investor_id' => $data['investor_id'],
        'share_class_category_id' => $data['share_class_category_id'],
        'share_class_id' => $data['share_class_id'],
    ];

    }

    /** TODO
    * Implement DAO method to return list of categories with total shares amount
    */
    public function categories(){

      $query = "SELECT scc.description as category, SUM(ct.diluted_shares) as total_shares
                FROM share_class_categories scc
                JOIN cap_table ct on scc.id = ct.share_class_category_id
                GROUP BY scc.id;";
       
      $stmt = $this->conn->prepare($query);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    /** TODO
    * Implement DAO method to delete investor
    */
    public function delete_investor($id){

      $query = "DELETE FROM investors WHERE id=:id";
      $stmt = $this->conn->prepare($query);
      
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $stmt->execute();


    }
}
?>
