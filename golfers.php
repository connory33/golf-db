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
    $total_sql = "SELECT COUNT(*) as total FROM players_w_stats_ranks";
    $total_result = mysqli_query($conn, $total_sql);
    $total_row = mysqli_fetch_assoc($total_result);
    $total_rows = $total_row['total'];
    $total_pages = ceil($total_rows / $limit);
    
    // Build and execute SQL query with pagination
    $sql = "SELECT * FROM players_w_stats_ranks ORDER BY lastName, firstName LIMIT $limit OFFSET $offset";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {

        ?>
        <div class='flex justify-between flex-wrap max-w-[80%] mx-auto'>
            <input type="text" id="searchByName" class="filter-input border rounded px-3 py-2 text-black" style='border-color: #0D2818' placeholder="Name">
            <input type="text" id="searchByCountry" class="filter-input border rounded px-3 py-2 text-black" style='border-color: #0D2818' placeholder="Country">
            <input type="text" id="searchByTour" class="filter-input border rounded px-3 py-2 text-black" style='border-color: #0D2818' placeholder="Tour">
            <!-- <input type="text" id="searchByDGRank" class="filter-input border rounded px-3 py-2 text-black" style='border-color: #0D2818' placeholder="DG Rank">
            <input type="text" id="searchByDGSkill" class="filter-input border rounded px-3 py-2 text-black" style='border-color: #0D2818' placeholder="DG Skill Rating">
            <input type="text" id="searchByOWGR" class="filter-input border rounded px-3 py-2 text-black" style='border-color: #0D2818' placeholder="OWGR Rank">
            <input type="text" id="searchByDriveAcc" class="filter-input border rounded px-3 py-2 text-black" style='border-color: #0D2818' placeholder="Driving Accuracy">
            <input type="text" id="searchByDriveDist" class="filter-input border rounded px-3 py-2 text-black" style='border-color: #0D2818' placeholder="Driving Distance">
            <input type="text" id="searchBySGApp" class="filter-input border rounded px-3 py-2 text-black" style='border-color: #0D2818' placeholder="SG Approach">
            <input type="text" id="searchBySGARG" class="filter-input border rounded px-3 py-2 text-black" style='border-color: #0D2818' placeholder="SG Around Green">
            <input type="text" id="searchBySGOTT" class="filter-input border rounded px-3 py-2 text-black" style='border-color: #0D2818' placeholder="SG Off the Tee">
            <input type="text" id="searchBySGPutt" class="filter-input border rounded px-3 py-2 text-black" style='border-color: #0D2818' placeholder="SG Putting">
            <input type="text" id="searchBySGTotal" class="filter-input border rounded px-3 py-2 text-black" style='border-color: #0D2818' placeholder="SG Total"> -->
        </div>
        <br>
        <div class='overflow-x-auto'>
            <table class='default-zebra-table text-white w-4/5 mx-auto'>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Country</th>
                        <th>Primary Tour</th>
                        <th>DG Rank</th>
                        <th>DG Skill Estimate</th>
                        <th>OWGR Rank</th>
                        <th>Last Updated (Ranks)</th>
                        <th>Driving Accuracy</th>
                        <th>Driving Distance</th>
                        <th>SG Approach</th>
                        <th>SG Around Green</th>
                        <th>SG Off the Tee</th>
                        <th>SG Putting</th>
                        <th>SG Total</th>
                        <th>Last Updated (Stats)</th>
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
                $last_updated_ranks = $row['last_updated_estimate_ranks'];

                $driving_accuracy = $row['driving_accuracy'];
                $driving_dist = $row['driving_distance'];
                $sg_approach = $row['sg_approach'];
                $sg_around_green = $row['sg_around_green'];
                $sg_off_the_tee = $row['sg_off_the_tee'];
                $sg_putting = $row['sg_putting'];
                $sg_total = $row['sg_total'];
                $last_updated_stats = $row['last_updated_stats'];


                echo "<tr>";
                echo "<td>" . $dg_id . "</td>";
                echo "<td>" . $name . "</td>";
                echo "<td>" . $country_code . "</td>";
                echo "<td>" . $primary_tour . "</td>";

                if ($datagolf_rank != null) {
                    echo "<td>" . $datagolf_rank . "</td>";
                    echo "<td>" . number_format($dg_skill_estimate, 3) . "</td>";
                    echo "<td>" . $owgr_rank . "</td>";
                    echo "<td>" . $last_updated_ranks . "</td>";
                } else {
                    echo "<td>-</td>";
                    echo "<td>-</td>";
                    echo "<td>-</td>";
                    echo "<td>-</td>";
                }

                if ($driving_accuracy != null) {
                    echo "<td>" . $driving_accuracy . "</td>";
                    echo "<td>" . $driving_dist . "</td>";
                    echo "<td>" . $sg_approach . "</td>";
                    echo "<td>" . $sg_around_green . "</td>";
                    echo "<td>" . $sg_off_the_tee . "</td>";
                    echo "<td>" . $sg_putting . "</td>";
                    echo "<td>" . $sg_total . "</td>";
                    echo "<td>" . $last_updated_stats . "</td>";
                } else {
                    echo "<td>-</td>";
                    echo "<td>-</td>";
                    echo "<td>-</td>";
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

            echo "</div>";

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



    // If no players are found for SQL     
    } else {
        echo "0 results";
    }
    ?>

    </div>
  



    <?php mysqli_close($conn);

    include 'footer.php'; ?>

  </body>

  <!-- Script to enable table filtering -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
    const filters = {
        name: document.getElementById('searchByName'),
        country: document.getElementById('searchByCountry'),
        tour: document.getElementById('searchByTour'),
    };

    const tableRows = document.querySelectorAll('.default-zebra-table tbody tr');

    function filterTable() {
        const nameVal = filters.name.value.toLowerCase();
        const countryVal = filters.country.value.toLowerCase();
        const tourVal = filters.tour.value.toLowerCase();

        tableRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const name = cells[1]?.textContent.toLowerCase() || '';
        const country = cells[2]?.textContent.toLowerCase() || '';
        const tour = cells[3]?.textContent.toLowerCase() || '';

        const show =
            name.includes(nameVal) &&
            country.includes(countryVal) &&
            tour.includes(tourVal);

        row.style.display = show ? '' : 'none';
        });
    }

    // Attach filter events
    Object.values(filters).forEach(input => {
        input.addEventListener('input', filterTable);
    });
    });
    </script>

</html>




