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
            $sortableColumns = [
                'playerName' => "CONCAT(p.firstName, ' ', p.lastName)",
                'fin_text' => 'a.fin_text',
                'sg_ott' => 'a.sg_ott',
                'sg_app' => 'a.sg_app', 
                'sg_arg' => 'a.sg_arg',
                'sg_putt' => 'a.sg_putt',
                'sg_t2g' => 'a.sg_t2g',
                'sg_total' => 'a.sg_total',
                // 'winnings' => 'a.winnings'
                // Note: total_score and to_par_total are removed as they're calculated fields
            ];

            $sort_by = $_GET['sort_by'] ?? 'fin_text';
            $order = $_GET['order'] ?? 'asc';

            // Check if we're sorting by a calculated field that needs PHP sorting
            $needsPhpSorting = in_array($sort_by, ['total_score', 'to_par_total']);

            // Only use SQL sorting for real database columns
            if (!$needsPhpSorting && isset($sortableColumns[$sort_by])) {
                $sortBySql = $sortableColumns[$sort_by];
                $orderSql = strtolower($order) === 'desc' ? 'DESC' : 'ASC';
                $orderByClause = "ORDER BY $sortBySql $orderSql";
            } else {
                // Default ordering if we'll sort in PHP
                $orderByClause = "ORDER BY a.fin_text ASC";
            }

            $sql = "SELECT 
                        a.dg_id,
                        a.event_round,
                        a.score,
                        a.toPar,
                        a.birdies,
                        a.bogies,
                        a.sg_ott,
                        a.sg_app,
                        a.sg_arg,
                        a.sg_putt,
                        a.sg_t2g,
                        a.sg_total,
                        a.fin_text,
                        -- a.winnings,
                        p.firstName,
                        p.lastName,
                        e.tour as tour,
                        e.event_name,
                        e.course_name,
                        e.season,
                        e.start_date
                    FROM all_historical_rounds a
                    LEFT JOIN players_w_stats_ranks p ON a.dg_id = p.dg_id
                    LEFT JOIN all_tours_events e ON a.event_id = e.event_id
                    WHERE a.event_id = ?
                    $orderByClause";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && mysqli_num_rows($result) > 0) {
                // Fetch the first row to get event details
                $row = mysqli_fetch_assoc($result); 
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

                // Reset the result pointer to the beginning
                mysqli_data_seek($result, 0);
            ?>

            <br>   
            <div class="mx-auto mt-4">
                
                <h1 class="text-3xl text-center">Event Details</h1>
                <h2 class="text-2xl text-center mt-4"><?= htmlspecialchars($event_name) ?> - <?= htmlspecialchars($course_name) ?> - <?= htmlspecialchars($season) ?> <?= htmlspecialchars($tour) ?></h2>
                <h3 class="text-xl text-center mt-2">Start Date: <?= htmlspecialchars($start_date) ?></h3>
                <br>
                <table id='main-sortable-table' class='scoreboard-table text-white max-w-[85%] mx-auto text-center'>
                    <colgroup>
                        <col class="scoreboard-player">
                        <col class="scoreboard-finish">
                        <col class="scoreboard-total-score">
                        <col class="scoreboard-to-par-total">
                        <col class="scoreboard-sg-ott">
                        <col class="scoreboard-sg-app">
                        <col class="scoreboard-sg-arg">
                        <col class="scoreboard-sg-putt">
                        <col class="scoreboard-sg-t2g">
                        <col class="scoreboard-sg-total">
                    </colgroup>
                    <thead>
                        <?php
                        // Create the sort link and apply CSS class based on state
                        function sort_link($label, $column, $currentSort, $currentOrder) {
                            $newOrder = ($currentSort == $column && $currentOrder == 'asc') ? 'desc' : 'asc';
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
                            <th class="<?= sort_class('playerName', $sort_by, $order) ?>"><?= sort_link('Player', 'playerName', $sort_by, $order) ?></th>
                            <th class="<?= sort_class('fin_text', $sort_by, $order) ?>"><?= sort_link('Finish', 'fin_text', $sort_by, $order) ?></th>
                            <th class="<?= sort_class('total_score', $sort_by, $order) ?>"><?= sort_link('Total Score', 'total_score', $sort_by, $order) ?></th>
                            <th class="<?= sort_class('to_par_total', $sort_by, $order) ?>"><?= sort_link('To Par Total', 'to_par_total', $sort_by, $order) ?></th>
                            <th class="<?= sort_class('sg_ott', $sort_by, $order) ?>"><?= sort_link('SG:OTT', 'sg_ott', $sort_by, $order) ?></th>
                            <th class="<?= sort_class('sg_app', $sort_by, $order) ?>"><?= sort_link('SG:APP', 'sg_app', $sort_by, $order) ?></th>
                            <th class="<?= sort_class('sg_arg', $sort_by, $order) ?>"><?= sort_link('SG:ARG', 'sg_arg', $sort_by, $order) ?></th>
                            <th class="<?= sort_class('sg_putt', $sort_by, $order) ?>"><?= sort_link('SG:PUTT', 'sg_putt', $sort_by, $order) ?></th>
                            <th class="<?= sort_class('sg_t2g', $sort_by, $order) ?>"><?= sort_link('SG:T2G', 'sg_t2g', $sort_by, $order) ?></th>
                            <th class="<?= sort_class('sg_total', $sort_by, $order) ?>"><?= sort_link('SG:Total', 'sg_total', $sort_by, $order) ?></th>
                        </tr>
                    </thead>
                    <tbody>

                <?php
                // Organize data by player
                $players = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $dg_id = $row['dg_id'];
                    
                    if (!isset($players[$dg_id])) {
                        $players[$dg_id] = [
                            'dg_id' => $dg_id,
                            'name' => $row['firstName'] . ' ' . $row['lastName'],
                            'finish' => $row['fin_text'],
                            'rounds' => []
                        ];
                    }
                    
                    $players[$dg_id]['rounds'][] = $row;
                }

                // Calculate totals for each player
                foreach ($players as $dg_id => &$player) {
                    $totalScore = 0;
                    $totalToPar = 0;
                    $totalSgOtt = 0;
                    $totalSgApp = 0;
                    $totalSgArg = 0;
                    $totalSgPutt = 0;
                    $totalSgT2g = 0;
                    $totalSgTotal = 0;

                    // Iterate through each round data and calculate totals
                    foreach ($player['rounds'] as $round) {
                        $totalScore += (int)$round['score'];
                        $totalToPar += (int)$round['toPar'];
                        $totalSgOtt += (float)$round['sg_ott'];
                        $totalSgApp += (float)$round['sg_app'];
                        $totalSgArg += (float)$round['sg_arg'];
                        $totalSgPutt += (float)$round['sg_putt'];
                        $totalSgT2g += (float)$round['sg_t2g'];
                        $totalSgTotal += (float)$round['sg_total'];
                    }

                    // Add the calculated totals to each player
                    $player['total_score'] = $totalScore;
                    $player['to_par_total'] = $totalToPar;
                    $player['total_sg_ott'] = $totalSgOtt;
                    $player['total_sg_app'] = $totalSgApp;
                    $player['total_sg_arg'] = $totalSgArg;
                    $player['total_sg_putt'] = $totalSgPutt;
                    $player['total_sg_t2g'] = $totalSgT2g;
                    $player['total_sg_total'] = $totalSgTotal;
                }

                // Sort players if needed by PHP (for calculated fields)
                if ($needsPhpSorting || !isset($sortableColumns[$sort_by])) {
                    usort($players, function ($a, $b) use ($sort_by, $order) {
                        $valueA = null;
                        $valueB = null;

                        switch ($sort_by) {
                            case 'playerName':
                                $valueA = $a['name'];
                                $valueB = $b['name'];
                                break;
                            case 'fin_text':
                                $valueA = $a['finish'];
                                $valueB = $b['finish'];
                                break;
                            case 'total_score':
                                $valueA = $a['total_score'];
                                $valueB = $b['total_score'];
                                break;
                            case 'to_par_total':
                                $valueA = $a['to_par_total'];
                                $valueB = $b['to_par_total'];
                                break;
                            case 'sg_ott':
                                $valueA = $a['total_sg_ott'];
                                $valueB = $b['total_sg_ott'];
                                break;
                            case 'sg_app':
                                $valueA = $a['total_sg_app'];
                                $valueB = $b['total_sg_app'];
                                break;
                            case 'sg_arg':
                                $valueA = $a['total_sg_arg'];
                                $valueB = $b['total_sg_arg'];
                                break;
                            case 'sg_putt':
                                $valueA = $a['total_sg_putt'];
                                $valueB = $b['total_sg_putt'];
                                break;
                            case 'sg_t2g':
                                $valueA = $a['total_sg_t2g'];
                                $valueB = $b['total_sg_t2g'];
                                break;
                            case 'sg_total':
                                $valueA = $a['total_sg_total'];
                                $valueB = $b['total_sg_total'];
                                break;
                            default:
                                $valueA = 0;
                                $valueB = 0;
                                break;
                        }

                        // Handle numeric vs string comparison
                        if (is_numeric($valueA) && is_numeric($valueB)) {
                            return $order === 'asc' ? $valueA <=> $valueB : $valueB <=> $valueA;
                        }
                        
                        // Convert to strings for comparison to avoid null issues
                        $valueA = (string)$valueA;
                        $valueB = (string)$valueB;
                        return $order === 'asc' ? $valueA <=> $valueB : $valueB <=> $valueA;
                    });
                }

                // Display the players table
                foreach ($players as $dg_id => $player) {
                    $playerName = htmlspecialchars($player['name']);
                    $fin_text = htmlspecialchars($player['finish']);
                    
                    echo "<tr class='cursor-pointer' onclick=\"toggleDetails('$dg_id')\">";
                    echo "<td><a href='golfer.php?dg_id=$dg_id'>$playerName</a></td>";
                    echo "<td>$fin_text</td>";
                    echo "<td>" . $player['total_score'] . "</td>";
                    echo "<td>" . $player['to_par_total'] . "</td>";
                    echo "<td>" . number_format($player['total_sg_ott'], 2) . "</td>";
                    echo "<td>" . number_format($player['total_sg_app'], 2) . "</td>";
                    echo "<td>" . number_format($player['total_sg_arg'], 2) . "</td>";
                    echo "<td>" . number_format($player['total_sg_putt'], 2) . "</td>";
                    echo "<td>" . number_format($player['total_sg_t2g'], 2) . "</td>";
                    echo "<td>" . number_format($player['total_sg_total'], 2) . "</td>";

                    echo "</tr>";

                    // Display details row (initially hidden)
                    echo "<tr id='details-$dg_id' class='hidden'>";
                    echo "<td colspan='11'>";
                    echo "<div class='p-1'>";
                    echo "<table class='w-full text-sm text-center'>";
                    echo "<thead><tr style='background-color:rgb(38, 101, 63); color: white;'>
                            <th>Round</th><th>Score</th><th>To Par</th><th>Birdies</th><th>Bogies</th>
                            <th>SG:OTT</th><th>SG:APP</th><th>SG:ARG</th><th>SG:PUTT</th><th>SG:T2G</th><th>SG:Total</th>
                        </tr></thead><tbody>";

                    // Sort rounds by event_round
                    usort($player['rounds'], function($a, $b) {
                        return $a['event_round'] - $b['event_round']; // Ascending order
                    });
                    
                    foreach ($player['rounds'] as $round) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($round['event_round']) . "</td>";
                        echo "<td>" . htmlspecialchars($round['score']) . "</td>";
                        echo "<td>" . htmlspecialchars($round['toPar']) . "</td>";
                        echo "<td>" . htmlspecialchars($round['birdies']) . "</td>";
                        echo "<td>" . htmlspecialchars($round['bogies']) . "</td>";
                        echo "<td>" . number_format($round['sg_ott'], 2) . "</td>";
                        echo "<td>" . number_format($round['sg_app'], 2) . "</td>";
                        echo "<td>" . number_format($round['sg_arg'], 2) . "</td>";
                        echo "<td>" . number_format($round['sg_putt'], 2) . "</td>";
                        echo "<td>" . number_format($round['sg_t2g'], 2) . "</td>";
                        echo "<td>" . number_format($round['sg_total'], 2) . "</td>";
                        echo "</tr>";
                    }

                    echo "</tbody></table></div></td></tr>";
                }

                echo "</tbody></table>";
                echo "</div>";
                echo "<br>";

                // Add pagination if needed - I'm keeping the original code but you need to define $page and $total_pages
                if (isset($page) && isset($total_pages)) {
                    // Pagination Controls - creates controls like "1 2 .. 10 11 12 .. 21 22"
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
                }
                
                echo "<br>";
                include 'footer.php';
            } else {
                echo "<p class='text-center'>No results found for this event.</p>";
                echo "</div>";
                echo "<br>";
                include 'footer.php';
            }
        } else {
            echo "<p class='text-center'>No event ID provided.</p>";
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

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const table = document.getElementById("main-sortable-table");
                const tbody = table.querySelector("tbody");
                const headers = table.querySelectorAll("thead th");
                let sortDirection = {};

                headers.forEach((header, index) => {
                if (header.classList.contains("no-sort")) return; // Skip unsortable headers

                sortDirection[index] = 1; // 1 = asc, -1 = desc

                header.style.cursor = "pointer";
                header.addEventListener("click", () => {
                    const rows = Array.from(tbody.querySelectorAll("tr"));
                    const dir = sortDirection[index];
                    sortDirection[index] *= -1;

                    const isNumeric = rows.every(row => {
                    const cell = row.children[index]?.textContent.trim();
                    return cell === "" || !isNaN(cell);
                    });

                    rows.sort((a, b) => {
                    let aText = a.children[index]?.textContent.trim() || "";
                    let bText = b.children[index]?.textContent.trim() || "";

                    if (isNumeric) {
                        return dir * (parseFloat(aText) - parseFloat(bText));
                    } else {
                        return dir * aText.localeCompare(bText);
                    }
                    });

                    rows.forEach(row => tbody.appendChild(row));
                });
                });
            });
        </script>
    </div>
  </body>
</html>