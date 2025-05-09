<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../../../favicon.ico">

    <title>Connor Young</title>


    <link href="../resources/css/default_golf.css" rel="stylesheet" type="text/css" />

       <script src="https://cdn.tailwindcss.com"></script>

  </head>
  <body>
     <?php include 'header.php'; ?>
    <div class='full-page-container'>

        <?php
        include('golf_db_connection.php');

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        // Check if the 'game_id' is passed in the URL
        if (isset($_GET['event_id'])) {
            $event_id = $_GET['event_id'];

               // Sorting
                $sortableColumns = ['playerName', 'fin_text', 'score', 'toPar', 'sg_ott', 'sg_app', 'sg_arg', 'sg_putt', 'sg_t2g', 'sg_total', 'winnings'];

                $sortBy = in_array($_GET['sort_by'] ?? '', $sortableColumns) ? $_GET['sort_by'] : 'fin_text';
                $order = ($_GET['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';


            // Player Stats Query
            $playerStatsQuery = "SELECT all_historical_rounds.*, 
                players_w_stats_ranks.firstName, players_w_stats_ranks.lastName,
                all_tours_events.tour, all_tours_events.event_name, all_tours_events.course_name, all_tours_events.season, all_tours_events.start_date
            FROM all_historical_rounds 
            LEFT JOIN players_w_stats_ranks ON all_historical_rounds.dg_id = players_w_stats_ranks.dg_id
            LEFT JOIN all_tours_events ON all_historical_rounds.event_id = all_tours_events.event_id
            WHERE all_historical_rounds.event_id = $event_id
            ORDER BY $sortBy $order";
            $playerStatsResult = mysqli_query($conn, $playerStatsQuery);
            
            
            $row = mysqli_fetch_assoc($playerStatsResult); // Fetch the first row to get event details
            $tour = $row['tour'];
            if ($tour == 'pga') {
                $tour = 'PGA Tour';
            } elseif ($tour == 'Euro') {
                $tour = 'European Tour';
            } elseif ($tour == 'kft') {
                $tour = 'Korn Ferry Tour';
            } elseif ($tour == 'liv') {
                $tour = 'LIV Golf Tour';
            }
            $event_name = $row['event_name'];
            $course_name = $row['course_name'];
            $season = $row['season'];
            $start_date = $row['start_date'];
            $start_date = date('F j, Y', strtotime($start_date)); // Format the date to "Month Day, Year"

            // reset the result pointer to the beginning
            mysqli_data_seek($playerStatsResult, 0); // Reset the result pointer to the beginning
            ?>

             <br>   
        <div class="mx-auto mt-4">
            
            <h1 class="text-3xl text-center">Event Details</h1>
            <h2 class="text-2xl text-center mt-4"><?= htmlspecialchars($event_name) ?> - <?= htmlspecialchars($course_name) ?> - <?= htmlspecialchars($season) ?> <?= htmlspecialchars($tour) ?></h2>
            <h3 class="text-xl text-center mt-2">Start Date: <?= htmlspecialchars($start_date) ?></h3>
            <br>
            <table class='scoreboard-table text-white max-w-[85%] mx-auto text-center'>
                <thead>
                    <?php
                    // Determine current sort
                    $sortBy = $_GET['sort_by'] ?? 'fin_text';
                    $order = $_GET['order'] ?? 'asc';

                    // Create the sort link and apply CSS class based on state
                    function sort_link($label, $column, $currentSort, $currentOrder) {
                        $newOrder = ($currentSort == $column && $currentOrder == 'asc') ? 'desc' : 'asc';
                        // $arrow = $currentSort == $column ? ($currentOrder == 'asc' ? ' ↑' : ' ↓') : '';
                        $query = $_GET;
                        $query['sort_by'] = $column;
                        $query['order'] = $newOrder;
                        $url = '?' . http_build_query($query);
                        return "<a href='$url' class='hover:underline'>$label</a>";
                    }

                    function sort_class($column, $currentSort, $currentOrder) {
                        if ($column !== $currentSort) return '';
                        return $currentOrder === 'asc' ? 'sort-asc' : 'sort-desc';
                    }
                    ?>
                    <tr>
                        <th class="<?= sort_class('player', $sortBy, $order) ?>"><?= sort_link('Player', 'playerName', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('finish', $sortBy, $order) ?>"><?= sort_link('Finish', 'fin_text', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('totalScore', $sortBy, $order) ?>"><?= sort_link('Total Score', 'score', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('toParTotal', $sortBy, $order) ?>"><?= sort_link('To Par Total', 'toPar', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('SG:OTT', $sortBy, $order) ?>"><?= sort_link('SG:OTT', 'sg_ott', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('SG:APP', $sortBy, $order) ?>"><?= sort_link('SG:APP', 'sg_app', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('SG:ARG', $sortBy, $order) ?>"><?= sort_link('SG:ARG', 'sg_arg', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('SG:PUTT', $sortBy, $order) ?>"><?= sort_link('SG:PUTT', 'sg_putt', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('SG:T2G', $sortBy, $order) ?>"><?= sort_link('SG:T2G', 'sg_t2g', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('SG:Total', $sortBy, $order) ?>"><?= sort_link('SG:Total', 'sg_total', $sortBy, $order) ?></th>
                        <th class="<?= sort_class('winnings', $sortBy, $order) ?>"><?= sort_link('Winnings', 'winnings', $sortBy, $order) ?></th>
                    </tr>
                </thead>
                <tbody>

            <?php
            $players = [];
            
            while ($row = mysqli_fetch_assoc($playerStatsResult)) {
                $players[$row['dg_id']]['name'] = $row['firstName'] . ' ' . $row['lastName'];
                $players[$row['dg_id']]['finish'] = $row['fin_text'];
                $players[$row['dg_id']]['rounds'][] = $row;
            }

            foreach ($players as $dg_id => $player) {
                $playerName = $player['name'];
                $fin_text = $player['finish'];
                
                echo "<tr class='cursor-pointer' onclick=\"toggleDetails('$dg_id')\">";
                echo "<td><a href='golfer.php?dg_id=$dg_id'>$playerName</a></td>";
                echo "<td>$fin_text</td>";
                echo "<td>" . array_sum(array_column($player['rounds'], 'score')) . "</td>";
                echo "<td>" . array_sum(array_map(function($round) { return $round['score'] - $round['course_par']; }, $player['rounds'])) . "</td>";
                echo "<td>" . array_sum(array_column($player['rounds'], 'sg_ott')) . "</td>";
                echo "<td>" . array_sum(array_column($player['rounds'], 'sg_app')) . "</td>";
                echo "<td>" . array_sum(array_column($player['rounds'], 'sg_arg')) . "</td>";
                echo "<td>" . array_sum(array_column($player['rounds'], 'sg_putt')) . "</td>";
                echo "<td>" . array_sum(array_column($player['rounds'], 'sg_t2g')) . "</td>";
                echo "<td>" . array_sum(array_column($player['rounds'], 'sg_total')) . "</td>";
                echo "<td></td>";
                echo "</tr>";

                echo "<tr id='details-$dg_id' class='hidden'>";
                echo "<td colspan='11'>";
                echo "<div class='p-1'>";
                echo "<table class='w-full text-sm text-center'>";
                echo "<thead><tr style='background-color:rgb(38, 101, 63); color: white;'>
                        <th>Round</th><th>Score</th><th>To Par</th><th>Birdies</th><th>Bogies</th>
                        <th>SG:OTT</th><th>SG:APP</th><th>SG:ARG</th><th>SG:PUTT</th><th>SG:T2G</th><th>SG:Total</th>
                    </tr></thead><tbody>";

                // Sort rounds by event_round (or another key if necessary)
                    usort($player['rounds'], function($a, $b) {
                        return $a['event_round'] - $b['event_round']; // Ascending order
                    });
                
                foreach ($player['rounds'] as $round) {
                    $to_par = $round['score'] - $round['course_par'];
                    echo "<tr>";
                    echo "<td>{$round['event_round']}</td>";
                    echo "<td>{$round['score']}</td>";
                    echo "<td>$to_par</td>";
                    echo "<td>{$round['birdies']}</td>";
                    echo "<td>{$round['bogies']}</td>";
                    echo "<td>{$round['sg_ott']}</td>";
                    echo "<td>{$round['sg_app']}</td>";
                    echo "<td>{$round['sg_arg']}</td>";
                    echo "<td>{$round['sg_putt']}</td>";
                    echo "<td>{$round['sg_t2g']}</td>";
                    echo "<td>{$round['sg_total']}</td>";
                    echo "</tr>";
                }

                echo "</tbody></table></div></td></tr>";
                }

                echo "</div>";
                echo "</tbody></table>";
                echo "</div>";
                echo "<br>";
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
                include 'footer.php';
        } else {
            echo "<p class='text-white text-center'>No event ID provided.</p>";
            echo "</div>";
            echo "<br>";
            include 'footer.php';
        }

                ?>

                    <script>
                function toggleDetails(playerId) {
                    const row = document.getElementById('details-' + playerId);
                    if (row.classList.contains('hidden')) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                }
                </script>

  </body>
</html>