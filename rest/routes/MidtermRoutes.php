<?php

require_once __DIR__."/../services/MidtermService.php";
Flight::set('midtermService', new MidtermService);

Flight::route('GET /connection-check', function(){
    /** TODO
    * This endpoint prints the message from constructor within MidtermDao class
    * Goal is to check whether connection is successfully established or not
    * This endpoint does not have to return output in JSON format
    */

    new MidtermDao();
});

Flight::route('GET /cap-table', function(){
    /** TODO
    * This endpoint returns list of all share classes within table named cap_table
    * Each class contains description field named 'class' and array of all categories within given class
    * Each category contains description field named 'category' and array of all investors that have shares within given category
    * Each investor has fields: 'diluted_shares' and 'investor' which is obtained by concatanation of first and last name of the investor
    * Outpus is given in figure 2
    * This endpoint should return output in JSON format
    */

       // Access the service
       $midtermService = Flight::get('midtermService');

       // Fetch the cap table data from the service
       $results = $midtermService->cap_table();
   
       // Initialize data structure
       $data = [];
   
       // Process the results to organize the data
       foreach ($results as $row) {
           $class = $row['class'];
           $category = $row['category'];
           $investor = $row['investor'];
           $diluted_shares = (int)$row['diluted_shares']; // Ensure the shares are treated as integers
   
           // Find or create the share class
           if (!isset($data[$class])) {
               $data[$class] = [
                   'class' => $class,
                   'categories' => []
               ];
           }
   
           // Find or create the category
           if (!isset($data[$class]['categories'][$category])) {
               $data[$class]['categories'][$category] = [
                   'category' => $category,
                   'investors' => []
               ];
           }
   
           // Check if the investor already exists in this category
           $found = false;
           foreach ($data[$class]['categories'][$category]['investors'] as &$existingInvestor) {
               if ($existingInvestor['investor'] === $investor) {
                   // Combine the shares for the same investor
                   $existingInvestor['diluted_shares'] += $diluted_shares;
                   $found = true;
                   break;
               }
           }
   
           // If the investor doesn't exist yet, add them
           if (!$found) {
               $data[$class]['categories'][$category]['investors'][] = [
                   'investor' => $investor,
                   'diluted_shares' => $diluted_shares
               ];
           }
       }
   
       // Convert associative array into indexed arrays for categories and classes
       $output = [];
       foreach ($data as $class) {
           $class['categories'] = array_values($class['categories']);
           foreach ($class['categories'] as &$category) {
               $category['investors'] = array_values($category['investors']);
           }
           $output[] = $class;
       }
   
       // Return the JSON output
       Flight::json($output);

});

Flight::route('POST /cap-table-record', function(){
    /** TODO
    * This endpoint is used to add new record to cap-table database table. If added successfully output should be the added array with the id of the new record
    * Example output is given in figure 3
    * This endpoint should return output in JSON format
    */

    $data = Flight::request()->data->getData();
    $newRecord = Flight::get('midtermService')->add_cap_table_record($data);
    Flight::json($newRecord);

});


Flight::route('GET /categories', function(){
    /** TODO
    * This endpoint returns list of all categories with the total amount of diluted_shares for each category
    * Output example is given in figure 4
    * This endpoint should return output in JSON format
    */

    $data = Flight::get('midtermService')->categories();
    Flight::json($data);

});

Flight::route("DELETE /investor/@id", function($id){
    /** TODO
    * This endpoint is used to delete investor
    * Endpoint should return the message whether investor has been deleted
    * This endpoint should return output in JSON format
    */

    $data = Flight::get('midtermService')->delete_investor($id);
    Flight::json(['message' => 'You have successfully deleted the investor!'], 200);

  /** if ($id == NULL || $id == '') {
     *   Flight::halt(500, "You have to provide a valid employee id!");
     * } 

     * Flight::get('midtermService')->delete_investor($id);
     * Flight::json(['message' => 'You have successfully deleted the investor!'], 200); */ 

});

?>
