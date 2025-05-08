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
    $sql = "SELECT player_list.dg_id, player_list.firstName, player_list.lastName, player_list.country_code, player_list.amateur,
        datagolf_rankings.datagolf_rank, datagolf_rankings.dg_skill_estimate, datagolf_rankings.owgr_rank,
        datagolf_rankings.primary_tour, datagolf_rankings.last_updated
        FROM player_list LEFT JOIN datagolf_rankings ON player_list.dg_id=datagolf_rankings.dg_id ORDER BY lastName, firstName";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {

        ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Country</th>
                    <th>DG Rank</th>
                    <th>DG Skill Estimate</th>
                    <th>OWGR Rank</th>
                    <th>Primary Tour</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>

        <?php
        while($row = mysqli_fetch_assoc($result)) {
            $dg_id = $row['dg_id'];
            $firstName = $row['firstName'];
            $lastName = $row['lastName'];
            $name = $firstName . " " . $lastName;
            $amateur = $row['amateur'];
            if ($amateur == 1) {
                $name = $name . " (A)";
            }
            // $country = $row['country'];
            $country_code = $row['country_code'];
            $datagolf_rank = $row['datagolf_rank'];
            $dg_skill_estimate = $row['dg_skill_estimate'];
            $owgr_rank = $row['owgr_rank'];
            $primary_tour = $row['primary_tour'];
            $last_updated = $row['last_updated'];

            echo "<tr>";
            echo "<td>" . $dg_id . "</td>";
            echo "<td>" . $name . "</td>";
            echo "<td>" . $country_code . "</td>";
            if ($datagolf_rank != null) {
                echo "<td>" . $datagolf_rank . "</td>";
                echo "<td>" . $dg_skill_estimate . "</td>";
                echo "<td>" . $owgr_rank . "</td>";
                echo "<td>" . $primary_tour . "</td>";
                echo "<td>" . $last_updated . "</td>";
            } else {
                echo "<td>-</td>";
                echo "<td>-</td>";
                echo "<td>-</td>";
                echo "<td>-</td>";
                echo "<td>-</td>";
            }

            echo "</tr>";
            
        }

            echo "</tbody>";
            echo "</table>";

    } else {
        echo "0 results";
    }
    ?>


    <!-- PGA SCHEDULE -->
    <?php
    // Function to get address from latitude and longitude using Google Maps API
    function getaddress($lat,$lng) {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false';
        $json = @file_get_contents($url);
        $data=json_decode($json);
        // $status = $data->status;
        // if($status=="OK")
        // {
        return $data->results[0]->formatted_address;
        // }
        // else
        // {
        // return false;
        // }
    }




    $sql = "SELECT * FROM pga_schedule ORDER BY start_date DESC";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {

        ?>
        <table>
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

    } else {
        echo "0 results";
    }
    ?>



    </div>



  



    <?php mysqli_close($conn);

    include 'footer.php'; ?>

  </body>
</html>