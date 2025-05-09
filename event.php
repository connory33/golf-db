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

            // // Build SQL query to fetch event details
            // $basicInfoQuery = "SELECT * FROM events WHERE event_id = ?";

            // Player Stats Query
            $playerStatsQuery = "SELECT all_historical_rounds.*, 
                players_w_stats_ranks.firstName, players_w_stats_ranks.lastName,
                all_tours_events.tour, all_tours_events.event_name, all_tours_events.course_name, all_tours_events.season
            FROM all_historical_rounds 
            LEFT JOIN players_w_stats_ranks ON all_historical_rounds.dg_id = players_w_stats_ranks.dg_id
            LEFT JOIN all_tours_events ON all_historical_rounds.event_id = all_tours_events.event_id
            WHERE all_historical_rounds.event_id = $event_id
            ORDER BY fin_text ASC";
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

            // reset the result pointer to the beginning
            mysqli_data_seek($playerStatsResult, 0); // Reset the result pointer to the beginning
            ?>

                
        <div class="mx-auto mt-4">
            <br>
            <h1 class="text-3xl text-center">Event Details</h1>
            <h2 class="text-2xl text-center mt-4"><?= htmlspecialchars($event_name) ?> - <?= htmlspecialchars($course_name) ?> - <?= htmlspecialchars($season) ?> <?= htmlspecialchars($tour) ?></h2>
            <br>
            <table class='default-zebra-table scoreboard-table text-white max-w-[85%] mx-auto text-center'>
                <thead>
                    <tr>
                        <th>Player</th>
                        <th>Finish</th>
                        <th>Round</th>
                        <th>Score</th>
                        <th>To Par</th>
                        <th>Start Hole</th>
                        <th>Tee Time</th>
                    </tr>
                </thead>
                <tbody>

            <?php
            while ($row = mysqli_fetch_assoc($playerStatsResult)) { // BUILD EXPANDABLE TABLES FOR EACH ROUND UNDER PLAYER FINISH ROW
                
                $dg_id = $row['dg_id'];
                $playerName = $row['firstName'] . ' ' . $row['lastName'];
                $fin_text = $row['fin_text'];
                $event_round = $row['event_round'];
                $start_hole = $row['start_hole'];
                $tee_time = $row['teetime'];

                $birdies = $row['birdies'];
                $bogies = $row['bogies'];
                $course_par = $row['course_par'];
                $doubles_or_worses = $row['doubles_or_worse'];
                $driving_accuracy = $row['driving_accuracy'];
                $driving_distance = $row['driving_distance'];
                $eagles_or_better = $row['eagles_or_better'];
                $gir = $row['gir'];
                $great_shots = $row['great_shots'];
                $pars = $row['pars'];
                $poor_shots = $row['poor_shots'];
                $prox_fw = $row['prox_fw'];
                $prox_rgh = $row['prox_rgh'];
                $score = $row['score'];
                $to_par = $score - $course_par;
                $scrambling = $row['scrambling'];
                $sg_app = $row['sg_app'];
                $sg_arg = $row['sg_arg'];
                $sg_ott = $row['sg_ott'];
                $sg_putt = $row['sg_putt'];
                $sg_t2g = $row['sg_t2g'];
                $sg_total = $row['sg_total'];
                

                echo "<tr>";
                echo "<td><a href='golfer.php?dg_id=$dg_id'>$playerName</a></td>";
                echo "<td>$fin_text</td>";
                echo "<td>$event_round</td>";
                echo "<td>$score</td>";
                echo "<td>$to_par</td>";
                echo "<td>$start_hole</td>";
                echo "<td>$tee_time</td>";
                echo "</tr>";
                
            }
                echo "</tbody>";
                echo "</table>"; // End the stats table
        }
        ?>
    

        </div>

        <?php include 'footer.php'; ?>

    </div>

  </body>
</html>