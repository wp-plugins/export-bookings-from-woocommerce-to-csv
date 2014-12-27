<?php 

/**
 * @package Export_Bookings_from_WooCommerce_to_CSV
 * @version 1.0.0
 */
/*
Plugin Name: Export Bookings from WooCommerce to CSV

*/
ini_set('display_errors',1);
/**
 * Main plugin class
 *
 * @since 0.1
 **/
class Ninja_Export_Bookings {
	
	/**
	 * Class contructor
	 *
	 * @since 0.1
	 **/
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );
		add_action( 'admin_init', array( $this, 'generate_csv' ) );
	}

	/**
	 * Add administration menus
	 *
	 * @since 0.1
	 **/
	public function add_admin_pages() {
		add_menu_page('Bookings Export', 'Bookings Export', 'manage_options', __FILE__, array( $this,'bookings_export_setting'), "export-booking-settings");
		
		add_submenu_page( 'edit.php?post_type=wc_booking', __( 'Export Bookings', 'export-bookings-to-csv' ), __( 'Export Bookings', 'export-bookings-to-csv' ), 'manage_options', 'export-bookings-to-csv', array( $this,'export_bookings_to_csv') );
	}
	
	/**
	 * Process content of CSV file
	 *
	 * @since 0.1
	 **/
	 public function export_bookings_to_csv(){
		echo '<h1>Export Bookings</h1>';
		
		$startArr = $this->getAllStartDate();
		$endArr = $this->getAllEndDate();
		$resourceArr = $this->getAllResources();
		// $this->pre($startArr);
	?>
		<div class="wrap">
			<h2>Export Bookings Information</h2>
			<form method="post" name="csv_exporter_form" action="" enctype="multipart/form-data">
				<?php wp_nonce_field( 'export-bookings-bookings_export', '_wpnonce-export-bookings-bookings_export' ); ?>
				<p><h3>Filter your export:</h3></p>
				<label>Start Date:</label>
				<select name="startdate" id="startdate">
					<option value="">Select Start Date</option>
					<?php foreach($startArr as $startKey => $startVal){?>
						<option value="<?php echo substr($startKey,0,8);?>"><?php echo $startVal;?></option>
					<?php }?>
				</select>
				<label>End Date:</label>
				<select name="enddate" id="enddate">
					<option value="">Select End Date</option>
					<?php foreach($endArr as $endKey => $endVal){?>
						<option value="<?php echo substr($endKey,0,8);?>"><?php echo $endVal;?></option>
					<?php }?>
				</select>
				<label>Resource:</label>
				<select name="resource" id="resource">
					<option value="">Select Resource</option>
					<?php foreach($resourceArr as $resource_key => $resource_val){?>
						<option value="<?php echo $resource_key;?>"><?php echo $resource_val;?></option>
					<?php }?>
				</select>
				
				<p class="submit"><input type="button" id="reset" name="Reset" value="Reset filter" /></p>
				<h3>Press the button below to export all bookings information.</h3>
				
				<p class="submit"><input type="submit" name="Submit" value="Export Bookings" /></p>
			</form>
		</div>
		<script>
			document.getElementById('reset').onclick = function() {
				// var mysel = document.getElementById('myselect');
				// var mysel = document.getElementById('myselect');
				document.getElementById('startdate').selectedIndex = 0;
				document.getElementById('enddate').selectedIndex = 0;
				document.getElementById('resource').selectedIndex = 0;
				// alert(mysel.value);
				return false;
			}
		</script>
		<?php 
	}
	
	public function getAllResources(){
		
		global $wpdb;
		
		// $meta_resources = $wpdb->get_results("select * from $wpdb->postmeta where meta_key='_booking_resource_id'");
		$resources = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type='bookable_resource'");
		
		$resourceArr = array();
		// foreach($meta_resources as $meta){
		foreach($resources as $resource){
			// $meta->meta_value;
			// $resource_data = $wpdb->get_var( "SELECT post_title FROM $wpdb->posts WHERE post_type='bookable_resource' and ID = " .$meta->meta_value);
			$resourceArr[$resource->ID] = $resource->post_title;
		}
		return $resourceArr;
	}
	
	public function getAllStartDate(){
		global $wpdb;
		
		$start_dates = $wpdb->get_results("select * from $wpdb->postmeta where meta_key='_booking_start'");
		
		$startArr = array();
			// $this->pre($start_dates);
		foreach( $start_dates as $start_date){
			$formatted = $this->getFormattedTime($start_date->meta_value, 'date');
			$startArr[$start_date->meta_value] = $formatted;
		}
		return $startArr;
		
	}
	
	public function getAllEndDate(){
		global $wpdb;
		
		$end_dates = $wpdb->get_results("select * from $wpdb->postmeta where meta_key='_booking_end'");
		
		$endArr = array();
			// $this->pre($start_dates);
		foreach( $end_dates as $end_date){
			$formatted = $this->getFormattedTime($end_date->meta_value, 'date');
			$endArr[$end_date->meta_value] = $formatted;
		}
		return $endArr;
		
	}
	 
	/**
	 * Settings page for bookings export
	 *
	 * @since 0.1
	 **/ 
	 public function bookings_export_setting(){
		echo '<h1>Bookings Export Settings</h1>';
	 }
	 
	 public function getFormattedTime($time , $format='datetime'){
	
		$year = substr($time, 0,4);
		$month = substr($time, 4,2);
		$date = substr($time, 6,2);
		$hour = substr($time, 8,2);
		$min = substr($time, 10,2);
		// $new_date = substr($time, 0, strpos($time, 'T',1));
		// $new_hour = substr($time, strpos($time, 'T',1) + 1, 8);
		// $new_time = $new_date . ' ' . $new_hour;
		
		// $new_format = date("M d, Y @ h:i A", strtotime($new_time));
		$new_format = '';
		if($format == 'datetime')
			$new_format = $year . '-' . $month . '-' . $date . ' ' . $hour . ':' . $min;
		if($format == 'time')
			$new_format =  $hour . ':' . $min;
		if($format == 'date')
			$new_format = $year . '-' . $month . '-' . $date;
		return $new_format;
	}
	
	public function generate_csv(){
		if ( isset( $_POST['_wpnonce-export-bookings-bookings_export'] ) ) {
			// echo 'here';
			check_admin_referer( 'export-bookings-bookings_export', '_wpnonce-export-bookings-bookings_export' );
			// echo 'hello';
			
			global $wpdb;
		
			$export = $wpdb->get_results("select * from $wpdb->posts p where p.post_type = 'wc_booking'");
			
			$data = array();
			
			$data[] = array('product', '# person', 'start date', 'end date','cost');
			// fetch the data
			
			// print_r($data);
			
			// $head = 'product | # person | start date | end date | cost';
			
			foreach ( $export as $ex ) 
			{
				$start_date = '';
				$end_date = '';
				$resource = '';
				$product_title = '';
				$persons = '';
				$cost = '';
				// echo $ex->post_title;
				$bookings = $wpdb->get_results("select * from $wpdb->postmeta pm where pm.post_id = $ex->ID");
				foreach($bookings as $booking){
					if($booking->meta_key == '_booking_start' ){
						// echo '<br/>';
						// echo $_POST['startdate'];
						// echo '<br/>';
						// echo substr($booking->meta_value,0,8);
						if($_POST['startdate'] && $_POST['startdate'] != substr($booking->meta_value,0,8)){
							break;
						}
						$start_date = $this->getFormattedTime($booking->meta_value);
					}
					if( $booking->meta_key == '_booking_end' ){
						if($_POST['enddate'] && $_POST['enddate'] != substr($booking->meta_value,0,8)){
							break;
						}
						$end_date = $this->getFormattedTime($booking->meta_value);
					}
					if( $booking->meta_key == '_booking_resource_id' ){
						if($_POST['resource'] && $_POST['resource'] != $booking->meta_value){
							break;
						}
						$resource = $booking->meta_value;
					}
					if($booking->meta_key == '_booking_product_id'){
						$product_title = get_the_title($booking->meta_value);
					}
					if($booking->meta_key == '_booking_persons'){
						// $persons = $booking->meta_value;
						$uns = unserialize($booking->meta_value);
						// print_r($uns);die;
						$persons = $uns[0];
					}
					if($booking->meta_key == '_booking_cost'){
						$cost = $booking->meta_value;
					}
					// echo '<br/>';
				}
				if($start_date && $end_date && $resource && $product_title && $persons && $cost){
					$data[] = array($product_title, $persons, $start_date, $end_date, $cost);
				}
					// fputcsv($output, $data);
			}
			// print_r($data);
			$this->array_to_csv_download($data);
			exit;
		}
	}
	
	function array_to_csv_download($array, $filename = "export.csv", $delimiter=",") {
		// echo 'here';
		ob_start();
		// open raw memory as file so no temp files needed, you might run out of memory though
		$f = fopen('php://output', 'w'); 
		// loop over the input array
		foreach ($array as $line) { 
			// generate csv lines from the inner arrays
			fputcsv($f, $line); 
		}
		fclose($f);
		// rewrind the "file" with the csv lines
		// fseek($f, 0);
		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=".$filename);
		// Disable caching
		header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
		header("Pragma: no-cache"); // HTTP 1.0
		header("Expires: 0"); // Proxies
		
		
	}
	 
	 public function pre($arr){
		echo '<pre>';
		print_r($arr);
		echo '</pre>';
	 }
	 
}

new Ninja_Export_Bookings;