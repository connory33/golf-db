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
    <br>
    <h1 class='text-3xl text-center'>Golfers</h1>
    <br>

    <?php 
    include('golf_db_connection.php');
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


    // PLAYERS TABLE
    // Set filters
    $nameFilter = isset($_GET['name']) ? trim($_GET['name']) : '';
    $countryFilter = isset($_GET['country']) ? trim($_GET['country']) : '';
    $tourFilter = isset($_GET['tour']) ? trim($_GET['tour']) : '';

    // Pagination
    $limit = 50;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $limit;

    // Build WHERE clause
    $whereClauses = [];
    if ($nameFilter !== '') {
        $whereClauses[] = "(CONCAT(firstName, ' ', lastName) LIKE '%" . mysqli_real_escape_string($conn, $nameFilter) . "%')";
    }
    if ($countryFilter !== '') {
        $whereClauses[] = "(country_code LIKE '%" . mysqli_real_escape_string($conn, $countryFilter) . "%')";
    }
    if ($tourFilter !== '') {
        $whereClauses[] = "(primary_tour LIKE '%" . mysqli_real_escape_string($conn, $tourFilter) . "%')";
    }

    $whereSQL = '';
    if (!empty($whereClauses)) {
        $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);
    }

    // Get total rows to calculate total pages
    $total_sql = "SELECT COUNT(*) as total FROM players_w_stats_ranks $whereSQL";
    $total_result = mysqli_query($conn, $total_sql);
    $total_row = mysqli_fetch_assoc($total_result);
    $total_rows = $total_row['total'];
    $total_pages = ceil($total_rows / $limit);

    // Sorting
    $sortableColumns = ['dg_id', 'firstName', 'lastName', 'country_code', 'primary_tour', 'datagolf_rank', 'dg_skill_estimate', 'owgr_rank', 'last_updated_estimate_ranks', 'driving_accuracy', 'driving_distance', 'sg_approach', 'sg_around_green', 'sg_off_the_tee', 'sg_putting', 'sg_total', 'last_updated_stats'];

    $sortBy = in_array($_GET['sort_by'] ?? '', $sortableColumns) ? $_GET['sort_by'] : 'lastName';
    $order = ($_GET['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';


    // Get data with custom sorting logic for datagolf_rank and owgr_rank
    $sql = "SELECT * FROM players_w_stats_ranks
            $whereSQL
            ORDER BY 
                CASE 
                    WHEN $sortBy = 'datagolf_rank' THEN IFNULL(datagolf_rank, 999999)
                    WHEN $sortBy = 'owgr_rank' THEN IFNULL(owgr_rank, 999999)
                    ELSE $sortBy
                END $order
            LIMIT $limit
            OFFSET $offset";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        ?>

        <div class='flex flex-wrap max-w-[85%] mx-auto'>
            <form id='filterForm' method="GET" class='flex flex-wrap max-w-[85%]'>
            <p class='mt-2 mr-5'>Filter by:</p>
            <input type="text" name="name" value="<?= htmlspecialchars($nameFilter) ?>" class="border rounded px-3 py-2 text-black mr-2" style='border-color: #0D2818' placeholder="Name">
            <input type="text" name="country" value="<?= htmlspecialchars($countryFilter) ?>" class="border rounded px-3 py-2 text-black mr-2" style='border-color: #0D2818' placeholder="Country">
            <input type="text" name="tour" value="<?= htmlspecialchars($tourFilter) ?>" class="border rounded px-3 py-2 text-black mr-2" style='border-color: #0D2818' placeholder="Tour">

            <input type='hidden' name='sort_by' value='<?= htmlspecialchars($sortBy) ?>'>
            <input type='hidden' name='order' value='<?= htmlspecialchars($order) ?>'>

            </form>
        </div>
        <br>
        <div class='overflow-x-auto'>
            <table class='default-zebra-table golfers-table text-white max-w-[88%] mx-auto'>
                <thead>
                    <?php
                    // Determine current sort
                    $sortBy = $_GET['sort_by'] ?? 'lastName';
                    $order = $_GET['order'] ?? 'asc';

                    // Create the sort link and apply CSS class based on state
                    function sort_link($label, $column, $currentSort, $currentOrder) {
                        $newOrder = ($currentSort == $column && $currentOrder == 'asc') ? 'desc' : 'asc';
                        $arrow = $currentSort == $column ? ($currentOrder == 'asc' ? ' ↑' : ' ↓') : '';
                        $query = $_GET;
                        $query['sort_by'] = $column;
                        $query['order'] = $newOrder;
                        $url = '?' . http_build_query($query);
                        return "<a href='$url' class='hover:underline'>$label$arrow</a>";
                    }

                    function sort_class($column, $currentSort, $currentOrder) {
                        if ($column !== $currentSort) return '';
                        return $currentOrder === 'asc' ? 'sort-asc' : 'sort-desc';
                    }
                    ?>
                    <tr>
                        <th class="<?= sort_class('dg_id', $sortBy, $order) ?>"><?= sort_link('ID', 'dg_id', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('lastName', $sortBy, $order) ?>"><?= sort_link('Name', 'lastName', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('country_code', $sortBy, $order) ?>"><?= sort_link('Country', 'country_code', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('primary_tour', $sortBy, $order) ?>"><?= sort_link('Primary Tour', 'primary_tour', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('datagolf_rank', $sortBy, $order) ?>"><?= sort_link('DG Rank', 'datagolf_rank', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('dg_skill_estimate', $sortBy, $order) ?>"><?= sort_link('DG Skill Estimate', 'dg_skill_estimate', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('owgr_rank', $sortBy, $order) ?>"><?= sort_link('OWGR Rank', 'owgr_rank', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('last_updated_estimate_ranks', $sortBy, $order) ?>"><?= sort_link('Last Updated (Ranks)', 'last_updated_estimate_ranks', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('driving_accuracy', $sortBy, $order) ?>"><?= sort_link('Driving Accuracy', 'driving_accuracy', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('driving_distance', $sortBy, $order) ?>"><?= sort_link('Driving Distance', 'driving_distance', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('sg_approach', $sortBy, $order) ?>"><?= sort_link('SG Approach', 'sg_approach', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('sg_around_green', $sortBy, $order) ?>"><?= sort_link('SG Around Green', 'sg_around_green', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('sg_off_the_tee', $sortBy, $order) ?>"><?= sort_link('SG Off the Tee', 'sg_off_the_tee', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('sg_putting', $sortBy, $order) ?>"><?= sort_link('SG Putting', 'sg_putting', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('sg_total', $sortBy, $order) ?>"><?= sort_link('SG Total', 'sg_total', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('last_updated_stats', $sortBy, $order) ?>"><?= sort_link('Last Updated (Stats)', 'last_updated_stats', $sortBy, $order) ?></th>
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
                    echo "<td>". Null . "</td>";
                    echo "<td>". Null . "</td>";
                    echo "<td>". Null . "</td>";
                    echo "<td>". Null . "</td>";
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
                    echo "<td>". Null . "</td>";
                    echo "<td>". Null . "</td>";
                    echo "<td>". Null . "</td>";
                    echo "<td>". Null . "</td>";
                    echo "<td>". Null . "</td>";
                    echo "<td>". Null . "</td>";
                    echo "<td>". Null . "</td>";
                    echo "<td>". Null . "</td>";
                }

                echo "</tr>";
                
            }

                echo "</tbody>";
                echo "</table>";

            echo "</div>";
            echo "<br>";
            echo "<p class='text-sm text-center'> (A) denotes amateur status. </p><br>";



            //Pagination Controls - creates controls like "1 2 .. 10 11 12 .. 21 22"
            echo "<div class='mt-4 text-white flex flex-wrap items-center justify-center gap-2'>";

            if ($page > 1) {
                echo "<form method='get' class='inline'><input type='hidden' name='page' value='" . ($page - 1) . "'><button type='submit' class='px-3 py-1 bg-gray-700 hover:bg-gray-600 rounded'>Prev</button></form>";
            }

            // Function to simplify link generation
            function page_button($i, $current) {
                $base = "px-3 py-1 rounded ";
                $style = $i == $current ? "bg-green-700 font-bold" : "bg-gray-700 hover:bg-gray-600";
                return "<form method='get' class='inline'><input type='hidden' name='page' value='$i'><button type='submit' class='$base $style'>$i</button></form>";
            }

            // Show first 2 pages always
            if ($total_pages <= 10) {
                for ($i = 1; $i <= $total_pages; $i++) {
                    echo page_button($i, $page);
                }
            } else {
                // Always show first 2 pages
                for ($i = 1; $i <= 2; $i++) {
                    echo page_button($i, $page);
                }

                if ($page > 5) {
                    echo "<span class='px-2'>...</span>";
                }

                // Show 2 pages before current, current, 2 after
                $start = max(3, $page - 2);
                $end = min($total_pages - 2, $page + 2);

                for ($i = $start; $i <= $end; $i++) {
                    echo page_button($i, $page);
                }

                if ($page < $total_pages - 4) {
                    echo "<span class='px-2'>...</span>";
                }

                // Always show last 2 pages
                for ($i = $total_pages - 1; $i <= $total_pages; $i++) {
                    echo page_button($i, $page);
                }
            }

            if ($page < $total_pages) {
                echo "<form method='get' class='inline'><input type='hidden' name='page' value='" . ($page + 1) . "'><button type='submit' class='px-3 py-1 bg-gray-700 hover:bg-gray-600 rounded'>Next</button></form>";
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
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('filterForm');

        // Trigger form submit on change for selects
        form.querySelectorAll('select').forEach(el => {
        el.addEventListener('change', () => form.submit());
        });

        // For text input, wait for typing to pause
        let typingTimer;
        const doneTypingInterval = 200; // ms

        form.querySelectorAll('input[type="text"]').forEach(input => {
        input.addEventListener('input', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => form.submit(), doneTypingInterval);
        });
        });
    });



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




