<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../../../favicon.ico">

    <title>Connor Young</title>

    <link rel="stylesheet" type="text/css" href="../resources/css/default_golf.css">

    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body>

    <?php include 'header.php'; ?>

    <div class='full-page-container'>

        <p class='text-sm'> (A) denotes amateur status. </p>


    <?php 
    include('golf_db_connection.php');
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


    // PLAYERS 

    // Pagination
    // Pagination settings
    $limit = 50; // number of rows per page
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $limit;

    // Get total rows to calculate total pages
    $total_sql = "SELECT COUNT(*) as total FROM pga_schedule";
    $total_result = mysqli_query($conn, $total_sql);
    $total_row = mysqli_fetch_assoc($total_result);
    $total_rows = $total_row['total'];
    $total_pages = ceil($total_rows / $limit);
    
    // Build and execute SQL query with pagination

    $sql = "SELECT * FROM pga_schedule ORDER BY start_date DESC LIMIT $limit OFFSET $offset"; // need to add other tours and add column to label tour
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {

        ?>
        <div class='overflow-x-auto'>
        <table class='default-zebra-table text-white w-4/5 mx-auto'>
            <thead>
                <tr>
                    <th>Season</th>
                    <th>Course Name</th>
                    <th>Course Key</th>
                    <th>Event ID</th>
                    <th>Event Name</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <!-- <th>Calc Location</th> -->
                    <th>Start Date</th>
                </tr>
            </thead>
            <tbody>

        <?php
        while($row = mysqli_fetch_assoc($result)) {
            $season = $row['season'];
            $course_name = $row['course_name'];
            $course_key = $row['course_key'];
            $event_id = $row['event_id'];
            $event_name = $row['event_name'];

            $latitude = $row['latitude'];
            $longitude = $row['longitude'];
            // $calcLocation = getaddress($latitude, $longitude);

            $start_date = $row['start_date'];

            echo "<tr>";
            echo "<td>" . $season . "</td>";
            echo "<td>" . $course_name . "</td>";
            echo "<td>" . $course_key . "</td>";
            echo "<td>" . $event_id . "</td>";
            echo "<td>" . $event_name . "</td>";
            echo "<td>" . $latitude . "</td>";
            echo "<td>" . $longitude . "</td>";
            // echo "<td>" . $calcLocation . "</td>";
            echo "<td>" . $start_date . "</td>";
            echo "</tr>";
        }

            echo "</tbody>";
            echo "</table>";

            // Pagination controls
            //Pagination Controls - creates controls like "1 2 .. 10 11 12 .. 21 22"
            echo "<div class='mt-4 text-white flex flex-wrap items-center justify-center gap-2'>";

            if ($page > 1) {
                echo "<a href='?page=" . ($page - 1) . "' class='px-3 py-1 bg-gray-700 hover:bg-gray-600 rounded'>Prev</a>";
            }

            // Function to simplify link generation
            function page_link($i, $current) {
                $base = "px-3 py-1 rounded ";
                $style = $i == $current ? "bg-green-700 font-bold" : "bg-gray-700 hover:bg-gray-600";
                return "<a href='?page=$i' class='$base $style'>$i</a>";
            }

            // Show first 2 pages always
            if ($total_pages <= 10) {
                for ($i = 1; $i <= $total_pages; $i++) {
                    echo page_link($i, $page);
                }
            } else {
                // Always show first 2 pages
                for ($i = 1; $i <= 2; $i++) {
                    echo page_link($i, $page);
                }

                if ($page > 5) {
                    echo "<span class='px-2'>...</span>";
                }

                // Show 2 pages before current, current, 2 after
                $start = max(3, $page - 2);
                $end = min($total_pages - 2, $page + 2);

                for ($i = $start; $i <= $end; $i++) {
                    echo page_link($i, $page);
                }

                if ($page < $total_pages - 4) {
                    echo "<span class='px-2'>...</span>";
                }

                // Always show last 2 pages
                for ($i = $total_pages - 1; $i <= $total_pages; $i++) {
                    echo page_link($i, $page);
                }
            }

            if ($page < $total_pages) {
                echo "<a href='?page=" . ($page + 1) . "' class='px-3 py-1 bg-gray-700 hover:bg-gray-600 rounded'>Next</a>";
            }

            echo "</div>";
            echo "<br>";

    } else {
        echo "0 results";
    }
    ?>



    </div>


    <?php mysqli_close($conn);

    include 'footer.php'; ?>

  </body>
</html>